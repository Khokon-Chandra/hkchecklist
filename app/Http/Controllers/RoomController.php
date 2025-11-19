<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        if ($request->boolean('assign_defaults')) {
            $added = $this->assignDefaultTasks($room);
            return redirect()->route('rooms.index')->with('ok', "Room added. Assigned $added default task(s).");
        }

        return redirect()->route('rooms.index')->with('ok', 'Room added.');
    }

    public function edit(Room $room)
    {

        return view('rooms.edit', [
            'room'        => $room
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'assign_defaults'   => ['sometimes', 'boolean'],
        ]);

        $room->update([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),

        ]);

        if ($request->boolean('assign_defaults')) {
            $added = $this->assignDefaultTasks($room);
            return redirect()->route('rooms.index')->with('ok', "Room updated. Assigned $added new default task(s).");
        }

        return redirect()->route('rooms.index')->with('ok', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('ok', 'Room deleted.');
    }



    private function assignDefaultTasks(Room $room): int
    {
        return DB::transaction(function () use ($room) {
            $defaultTaskIds = Task::query()
                ->where('is_default', true)
                ->where('type', 'room')
                ->pluck('id')
                ->all();

            if (empty($defaultTaskIds)) {
                return 0;
            }

            $alreadyAttached = $room->tasks()->pluck('tasks.id')->all();

            $toAttach = array_values(array_diff($defaultTaskIds, $alreadyAttached));
            if (empty($toAttach)) {
                return 0;
            }

            $nextSort = (int) $room->tasks()->max('room_task.sort_order');
            $attachPayload = [];
            foreach ($toAttach as $taskId) {
                $attachPayload[$taskId] = [
                    'sort_order'           => ++$nextSort,
                    'instructions'         => null,
                    'visible_to_owner'     => true,
                    'visible_to_housekeeper' => true,
                ];
            }

            $room->tasks()->syncWithoutDetaching($attachPayload);

            return count($toAttach);
        });
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
}
