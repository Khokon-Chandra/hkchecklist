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

        return view('rooms.index', [
            'rooms'       => $rooms,
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
}
