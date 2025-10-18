<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Property $property, Room $room)
    {
        $this->authorize('view', $property);
        $this->assertRoomBelongs($room, $property);

        $tasks = $room->tasks()->orderBy('name')->paginate(30);

        return view('tasks.index', [
            'property'    => $property,
            'room'        => $room,
            'tasks'       => $tasks,
            'navProperty' => $property,
            'navRoom'     => $room,
        ]);
    }

    public function create(Property $property, Room $room)
    {
        $this->authorize('update', $property);
        $this->assertRoomBelongs($room, $property);

        return view('tasks.create', [
            'property'    => $property,
            'room'        => $room,
            'navProperty' => $property,
            'navRoom'     => $room,
        ]);
    }

    public function store(Request $request, Property $property, Room $room)
    {
        $this->authorize('update', $property);
        $this->assertRoomBelongs($room, $property);

        $data = $request->validate([
            'name'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'name')->where(fn($q) => $q->where('room_id', $room->id))
            ],
            'type'       => ['required', Rule::in(['room', 'inventory'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        Task::create([
            'property_id' => $property->id,
            'room_id'     => $room->id,
            'name'        => $data['name'],
            'type'        => $data['type'],
            'is_default'  => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('tasks.index', [$property, $room])->with('ok', 'Task added.');
    }

    public function edit(Property $property, Room $room, Task $task)
    {
        $this->authorize('update', $property);
        $this->assertRoomBelongs($room, $property);
        $this->assertTaskBelongs($task, $room);

        return view('tasks.edit', [
            'property'    => $property,
            'room'        => $room,
            'task'        => $task,
            'navProperty' => $property,
            'navRoom'     => $room,
        ]);
    }

    public function update(Request $request, Property $property, Room $room, Task $task)
    {
        $this->authorize('update', $property);
        $this->assertRoomBelongs($room, $property);
        $this->assertTaskBelongs($task, $room);

        $data = $request->validate([
            'name'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'name')
                    ->where(fn($q) => $q->where('room_id', $room->id))
                    ->ignore($task->id)
            ],
            'type'       => ['required', Rule::in(['room', 'inventory'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $task->update([
            'name'       => $data['name'],
            'type'       => $data['type'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('tasks.index', [$property, $room])->with('ok', 'Task updated.');
    }

    private function assertRoomBelongs(Room $room, Property $property): void
    {
        abort_unless($room->property_id === $property->id, 404);
    }

    private function assertTaskBelongs(Task $task, Room $room): void
    {
        abort_unless($task->room_id === $room->id, 404);
    }
}
