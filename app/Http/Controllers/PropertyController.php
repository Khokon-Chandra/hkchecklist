<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


class PropertyController extends Controller
{


    public function index(Request $request)
    {
        $user = $request->user();
        $q    = (string) $request->query('q', '');

        $query = Property::query();

        if ($user->hasRole('owner')) {
            $query->where('owner_id', $user->id);
        } elseif ($user->hasRole('housekeeper')) {
            $query->whereIn('id', function ($sub) use ($user) {
                $sub->select('property_id')
                    ->from('cleaning_sessions')
                    ->where('housekeeper_id', $user->id);
            });
        }

        $properties = $query
            ->when($q !== '', fn($qry) => $qry->where('name', 'like', "%{$q}%"))
            ->with(['owner'])
            ->withCount('rooms')
            ->orderBy('name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('properties.index', compact('properties'));
    }

    public function create()
    {

        return view('properties.create', [
            'owners' => User::role('owner')->pluck('name', 'id')->all()
        ]);
    }

    public function store(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user->hasRole('admin');

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50'],
            'photo'        => ['nullable', 'image', 'max:5120'], // 5MB
            'owner_id'     => $isAdmin ? ['required', Rule::exists('users', 'id')] : ['nullable'],
            'attach'       => ['nullable', Rule::in(['none', 'rooms', 'rooms_tasks'])],
        ]);

        if (! $isAdmin) {
            $data['owner_id'] = $user->id;
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }

        $attach = $request->input('attach', 'none'); // none | rooms | rooms_tasks

        DB::transaction(function () use ($data, $attach) {
            /** @var Property $property */
            $property = Property::create($data);

            if ($attach === 'none') {
                // Done—no room/task work requested.
                return;
            }

            // -------- Attach DEFAULT ROOMS --------
            // global Room templates with is_default = true
            $defaultRooms = Room::query()
                ->where('is_default', true)
                ->orderBy('name')
                ->get(['id']);

            if ($defaultRooms->isNotEmpty()) {
                $nextOrder = (int) $property->rooms()->max('property_room.sort_order');
                $nextOrder = $nextOrder > 0 ? $nextOrder + 1 : 1;

                $payload = [];
                foreach ($defaultRooms as $r) {
                    // avoid duplicates if any pre-exist
                    if (! $property->rooms()->where('rooms.id', $r->id)->exists()) {
                        $payload[$r->id] = ['sort_order' => $nextOrder++];
                    }
                }
                if (!empty($payload)) {
                    $property->rooms()->attach($payload);
                }
            }

            if ($attach !== 'rooms_tasks') {
                // Caller only wanted default rooms, not tasks.
                return;
            }

            // -------- Attach DEFAULT TASKS to those rooms --------
            // Reload the rooms that are now attached to this property and eager-load their default tasks.
            $rooms = $property->rooms()->with(['tasks' => function ($q) {
                $q->where('tasks.is_default', true)->orderBy('tasks.name');
            }])->get();

            foreach ($rooms as $room) {
                if ($room->tasks->isEmpty()) continue;

                $tNext = (int) $room->tasks()->max('room_task.sort_order');
                $tNext = $tNext > 0 ? $tNext + 1 : 1;

                $taskAttach = [];
                foreach ($room->tasks as $task) {
                    // syncWithoutDetaching avoids duplicates; include sort_order pivot
                    $taskAttach[$task->id] = ['sort_order' => $tNext++];
                }
                if (!empty($taskAttach)) {
                    $room->tasks()->syncWithoutDetaching($taskAttach);
                }
            }
        });

        return redirect()
            ->route('properties.index')
            ->with('success', match ($attach) {
                'rooms'        => 'Property created and default rooms assigned.',
                'rooms_tasks'  => 'Property created with default rooms & tasks assigned.',
                default        => 'Property created successfully.',
            });
    }

    public function edit(Property $property)
    {

        return view('properties.edit', [
            'property' => $property,
            'owners' => User::role('owner')->pluck('name', 'id')->all()
        ]);
    }

    public function update(Request $request, Property $property)
    {

        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50'],
            'photo'        => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];

        if ($isAdmin) {
            // Admin can reassign owner
            $rules['owner_id'] = ['required', Rule::exists('users', 'id')];
        } else {
            // Non-admin cannot set/override owner_id
            // If you’re on Laravel 12, you can prohibit it like this:
            $rules['owner_id'] = ['prohibited'];
        }

        $data = $request->validate($rules);

        if (!$isAdmin) {
            // Guardrail: keep property with the same owner (should also be in a Policy)
            if ($property->owner_id !== $user->id) {
                abort(403, 'You cannot modify properties you do not own.');
            }
            unset($data['owner_id']); // ensure not mass-assigned
        }

        // Remove existing photo if requested
        if ($request->boolean('remove_photo') && $property->photo_path) {
            Storage::disk('public')->delete($property->photo_path);
            $data['photo_path'] = null;
        }

        // Replace with newly uploaded photo
        if ($request->hasFile('photo')) {
            if ($property->photo_path) {
                Storage::disk('public')->delete($property->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }

        $property->update($data);

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {

        $property->delete();

        return redirect()->route('properties.index')->with('ok', 'Property deleted.');
    }



    public function rooms(Property $property)
    {

        $rooms = $property->rooms()
            ->withCount('tasks')
            ->orderBy('property_room.sort_order')
            ->paginate(20);

        return view('properties.rooms.index', [
            'property'    => $property,
            'rooms'       => $rooms,
            'navProperty' => $property,
        ]);
    }


    public function createRoom(Property $property)
    {

        return view('properties.rooms.create', [
            'property'    => $property,
            'navProperty' => $property,
        ]);
    }


    public function editRoom(Property $property, Room $room)
    {

        return view('properties.rooms.edit', [
            'property'    => $property,
            'room'        => $room,
            'navProperty' => $property,
        ]);
    }

    public function updateRoom(Request $request, Property $property, Room $room)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_default' => ['nullable', 'in:1'],
        ]);

        $newName = trim($data['name']);
        $isDefault = (bool) ($data['is_default'] ?? false);

        // Find an existing room by new name (case-insensitive) that's NOT the current one
        $existing = \App\Models\Room::whereRaw('LOWER(name) = ?', [mb_strtolower($newName)])
            ->where('id', '!=', $room->id)
            ->first();

        if ($existing) {
            // SWITCH attachment to the existing room (preserve pivot sort_order)
            $currentSort = (int) $property->rooms()->where('rooms.id', $room->id)->first()->pivot->sort_order ?? 0;

            // Detach old if attached
            $property->rooms()->detach($room->id);

            // Attach new if not attached
            if (! $property->rooms()->where('rooms.id', $existing->id)->exists()) {
                $property->rooms()->attach($existing->id, ['sort_order' => $currentSort ?: ($property->rooms()->max('property_room.sort_order') + 1)]);
            }

            // Optionally update default flag on target template
            $existing->is_default = $isDefault;
            $existing->save();

            return redirect()->route('properties.rooms.index', $property)
                ->with('status', "Switched to existing room: {$existing->name}");
        }

        // No existing match → update this room’s data (global)
        $room->name = $newName;
        $room->is_default = $isDefault;
        $room->save();

        return redirect()->route('properties.rooms.index', $property)
            ->with('status', "Updated room: {$room->name}");
    }

    public function destroyRoom(Property $property, Room $room)
    {
        // Detach room from property
        $property->rooms()->detach($room->id);

        return redirect()->route('properties.rooms.index', $property)
            ->with('status', 'Room detached from property.');
    }




    public function tasks(Property $property, Room $room)
    {
        abort_unless($property->rooms()->where('rooms.id', $room->id)->exists(), 403, 'Room does not belong to the specified property.');

        $tasks = $room->tasks()
            ->with(['media' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('room_task.sort_order')
            ->get();


        return view('properties.tasks.index', [
            'property'    => $property,
            'room'        => $room,
            'tasks'       => $tasks,
            'navProperty' => $property,
        ]);
    }


    public function createTask(Property $property, Room $room)
    {
        abort_unless($property->rooms()->where('rooms.id', $room->id)->exists(), 403, 'Room does not belong to the specified property.');

        return view('properties.tasks.create', [
            'property'    => $property,
            'room'        => $room,
            'navProperty' => $property,
        ]);
    }





    public function storeTask(Request $request, Property $property, Room $room)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:160'],
            'type'         => ['required', Rule::in(['room', 'inventory'])],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'visible_to_owner'       => ['nullable', 'boolean'],
            'visible_to_housekeeper' => ['nullable', 'boolean'],
            'media.*'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:20480'], // 20MB
            'captions.*'   => ['nullable', 'string', 'max:255'],
        ]);

        // find-or-create Task by case-insensitive name
        $name = trim($validated['name']);
        $task = Task::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if (!$task) {
            $task = Task::create([
                'name'       => $name,
                'type'       => $validated['type'],
                'is_default' => false,
            ]);
        } else {
            // Optionally adopt chosen type if empty; otherwise keep existing
            $task->type = $task->type ?: $validated['type'];
            $task->save();
        }

        // attach to room with next sort + pivot fields
        if (!$room->tasks()->where('tasks.id', $task->id)->exists()) {
            $nextOrder = (int)$room->tasks()->max('room_task.sort_order') + 1;
            $room->tasks()->attach($task->id, [
                'sort_order' => $nextOrder,
                'instructions' => $validated['instructions'] ?? null,
                'visible_to_owner' => (bool)($validated['visible_to_owner'] ?? true),
                'visible_to_housekeeper' => (bool)($validated['visible_to_housekeeper'] ?? true),
            ]);
        }

        // handle media (optional)
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $i => $file) {
                if (!$file) continue;
                $path = $file->store('task-media', 'public');
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'video') ? 'video' : 'image';

                $task->media()->create([
                    'type'       => $type,
                    'url'        => Storage::disk('public')->url($path),
                    'thumbnail'  => $type === 'image' ? Storage::disk('public')->url($path) : null,
                    'caption'    => $request->input("captions.$i"),
                    'sort_order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('properties.tasks.index', [$property, $room])
            ->with('status', "Task attached: {$task->name}");
    }

    public function editTask(Property $property, Room $room, Task $task)
    {
        abort_unless($property->rooms()->where('rooms.id', $room->id)->exists(), 403, 'Room does not belong to the specified property.');
        abort_unless($room->tasks()->where('tasks.id', $task->id)->exists(), 404, 'Task not found in the specified room.');

        $task->load(['media' => fn($q) => $q->orderBy('sort_order')]);
        $pivot = $room->tasks()->where('tasks.id', $task->id)->firstOrFail()->pivot;

        return view('properties.tasks.edit', [
            'property' => $property,
            'room'     => $room,
            'task'     => $task,
            'pivot'    => $pivot,
        ]);
    }

    public function updateTask(Request $request, Property $property, Room $room, Task $task)
    {
        abort_unless($property->rooms()->where('rooms.id', $room->id)->exists(), 403, 'Room does not belong to the specified property.');
        abort_unless($room->tasks()->where('tasks.id', $task->id)->exists(), 404, 'Task not found in the specified room.');

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:160'],
            'type'         => ['required', Rule::in(['room', 'inventory'])],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'visible_to_owner'       => ['nullable', 'boolean'],
            'visible_to_housekeeper' => ['nullable', 'boolean'],
        ]);

        $newName = trim($data['name']);

        // switch to existing task if name matches another
        $existing = Task::whereRaw('LOWER(name) = ?', [mb_strtolower($newName)])
            ->where('id', '!=', $task->id)->first();

        $currentSort = (int) $room->tasks()->where('tasks.id', $task->id)->first()->pivot->sort_order ?? 0;


        if ($existing) {
            // detach old, attach existing (preserve order)
            $room->tasks()->detach($task->id);
            if (!$room->tasks()->where('tasks.id', $existing->id)->exists()) {
                $room->tasks()->attach($existing->id, [
                    'sort_order' => $currentSort ?: ($room->tasks()->max('room_task.sort_order') + 1),
                    'instructions' => $data['instructions'] ?? null,
                    'visible_to_owner' => (bool)($data['visible_to_owner'] ?? true),
                    'visible_to_housekeeper' => (bool)($data['visible_to_housekeeper'] ?? true),
                ]);
            } else {
                $room->tasks()->updateExistingPivot($existing->id, [
                    'instructions' => $data['instructions'] ?? null,
                    'visible_to_owner' => (bool)($data['visible_to_owner'] ?? true),
                    'visible_to_housekeeper' => (bool)($data['visible_to_housekeeper'] ?? true),
                ]);
            }
            // Update chosen type if empty; else keep existing’s type
            $existing->type = $existing->type ?: $data['type'];
            $existing->save();

            return redirect()->route('properties.tasks.index', [$property, $room])
                ->with('status', "Switched to task: {$existing->name}");
        }

        // update current task + pivot
        $task->update(['name' => $newName, 'type' => $data['type']]);
        $room->tasks()->updateExistingPivot($task->id, [
            'instructions' => $data['instructions'] ?? null,
            'visible_to_owner' => (bool)($data['visible_to_owner'] ?? true),
            'visible_to_housekeeper' => (bool)($data['visible_to_housekeeper'] ?? true),
        ]);
        return redirect()->route('properties.tasks.index', [$property, $room])
            ->with('status', "Task updated: {$task->name}");
    }



    public function detachTask(Property $property, Room $room, Task $task)
    {
        $room->tasks()->detach($task->id);
        return redirect()->route('properties.tasks.index', [$property, $room])
            ->with('status', "Detached task: {$task->name}");
    }
}
