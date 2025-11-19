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
}
