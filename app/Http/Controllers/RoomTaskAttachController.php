<?php
// app/Http/Controllers/RoomTaskAttachController.php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTaskAttachController extends Controller
{
    public function store(Request $request, Room $room)
    {
        $data = $request->validate([
            'task_ids'    => ['array'],
            'task_ids.*'  => ['integer', 'exists:tasks,id'],
            'task_names'  => ['array'],
            'task_names.*'=> ['string', 'max:255'],
        ]);

        $taskIds   = $data['task_ids']   ?? [];
        $taskNames = $data['task_names'] ?? [];

        // create tasks for free-text names (optional: set defaults)
        foreach ($taskNames as $name) {
            $name = trim($name);
            if ($name === '') continue;
            $task = Task::firstOrCreate(
                ['name' => $name],
                ['is_default' => false, 'type' => 'room'] // adjust as needed
            );
            $taskIds[] = $task->id;
        }

        if (empty($taskIds)) {
            return back()->with('warn', 'No tasks selected.');
        }

        DB::transaction(function () use ($room, $taskIds) {
            $already = $room->tasks()->pluck('tasks.id')->all();
            $toAttach = array_values(array_diff($taskIds, $already));
            if (empty($toAttach)) return;

            $nextSort = (int) $room->tasks()->max('room_task.sort_order');
            $payload = [];
            foreach ($toAttach as $id) {
                $payload[$id] = [
                    'sort_order' => ++$nextSort,
                    'instructions' => null,
                    'visible_to_owner' => true,
                    'visible_to_housekeeper' => true,
                ];
            }
            $room->tasks()->syncWithoutDetaching($payload);
        });

        return back()->with('ok', 'Tasks attached to room.');
    }
}
