<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{

    public function index(Request $request)
    {

        $rooms = Room::withCount('tasks')
            ->when($request->search ?? false, function ($query, $search) {
                $query->where('name', 'like', "%$search%");
            })
            ->latest()
            ->paginate(20);

        $tasks = Task::orderBy('type')->orderBy('name')->get([
            'id',
            'name',
            'type',
            'is_default',
        ]);

        return view('rooms.index', [
            'rooms'       => $rooms,
            'tasks'       => $tasks
        ]);
    }

    public function create()
    {

        return view('rooms.create');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'assign_defaults'   => ['sometimes', 'boolean']
        ]);

        $room = Room::create([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('rooms.index')->with('ok', 'Room added.');
    }

    public function edit(Room $room)
    {

        // Load all tasks that can be assigned to a room
        $tasks = Task::orderBy('type')->orderBy('name')->get(['id', 'name', 'type', 'is_default']);

        // Load current tasks for this room
        $room->load('tasks');

        return view('rooms.edit', [
            'room'  => $room,
            'tasks' => $tasks,
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],

            // tasks from the multi-select
            'task_ids'   => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ]);

        $room->update([
            'name'       => $validated['name'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        // Attach / detach tasks in the pivot table
        $room->tasks()->sync($validated['task_ids'] ?? []);

        return redirect()
            ->route('rooms.index')
            ->with('ok', 'Room & tasks updated.');
    }


    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('ok', 'Room deleted.');
    }


    public function bulkAttachTasks(Request $request)
    {
        $validated = $request->validate([
            'room_ids'   => ['required', 'array', 'min:1'],
            'room_ids.*' => ['integer', 'exists:rooms,id'],
            'task_ids'   => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ]);

        $rooms = Room::whereIn('id', $validated['room_ids'])->get();

        foreach ($rooms as $room) {
            // assumes many-to-many relationship: Room::tasks()
            $room->tasks()->syncWithoutDetaching($validated['task_ids']);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()
            ->route('rooms.index')
            ->with('ok', 'Tasks assigned to selected rooms.');
    }

    /**
     * GET /rooms/{room}/tasks
     * Show tasks for a specific room with ability to edit, add, and reorder.
     */
    public function tasks(Room $room)
    {
        $tasks = $room->tasks()
            ->withPivot(['sort_order', 'instructions', 'visible_to_owner', 'visible_to_housekeeper'])
            ->with('media')
            ->orderBy('room_task.sort_order')
            ->get();

        return view('rooms.tasks.index', [
            'room' => $room,
            'tasks' => $tasks,
        ]);
    }

    /**
     * GET /rooms/{room}/tasks/create
     */
    public function createTask(Room $room)
    {
        return view('rooms.tasks.create', [
            'room' => $room,
        ]);
    }

    /**
     * POST /rooms/{room}/tasks
     */
    public function storeTask(Request $request, Room $room)
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

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $i => $file) {
                if (!$file) continue;

                $path = $file->store('task-media', 'public');
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'video') ? 'video' : 'image';

                $task->media()->create([
                    'type'       => $type,
                    'url'        => $path,
                    'thumbnail'  => $type === 'image' ? $path : null,
                    'caption'    => $request->input("captions.$i"),
                    'sort_order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('rooms.tasks.index', $room)
            ->with('status', "Task attached: {$task->name}");
    }

    /**
     * GET /rooms/{room}/tasks/{task}/edit
     */
    public function editTask(Room $room, Task $task)
    {
        abort_unless($room->tasks()->where('tasks.id', $task->id)->exists(), 404, 'Task not found in the specified room.');

        $task->load(['media' => fn($q) => $q->orderBy('sort_order')]);
        $pivot = $room->tasks()->where('tasks.id', $task->id)->firstOrFail()->pivot;

        return view('rooms.tasks.edit', [
            'room' => $room,
            'task' => $task,
            'pivot' => $pivot,
        ]);
    }

    /**
     * PUT /rooms/{room}/tasks/{task}
     */
    public function updateTask(Request $request, Room $room, Task $task)
    {
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
            // Update chosen type if empty; else keep existing's type
            $existing->type = $existing->type ?: $data['type'];
            $existing->save();

            return redirect()->route('rooms.tasks.index', $room)
                ->with('status', "Switched to task: {$existing->name}");
        }

        // update current task + pivot
        $task->update(['name' => $newName, 'type' => $data['type']]);
        $room->tasks()->updateExistingPivot($task->id, [
            'instructions' => $data['instructions'] ?? null,
            'visible_to_owner' => (bool)($data['visible_to_owner'] ?? true),
            'visible_to_housekeeper' => (bool)($data['visible_to_housekeeper'] ?? true),
        ]);
        return redirect()->route('rooms.tasks.index', $room)
            ->with('status', "Task updated: {$task->name}");
    }

    /**
     * DELETE /rooms/{room}/tasks/{task}
     */
    public function detachTask(Room $room, Task $task)
    {
        $room->tasks()->detach($task->id);
        return redirect()->route('rooms.tasks.index', $room)
            ->with('status', "Detached task: {$task->name}");
    }
}
