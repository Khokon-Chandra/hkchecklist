<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    /**
     * Toggle the checked state of a checklist item for a specific (session, room, task).
     * Requires: the room belongs to the session's property AND the task is attached to that room.
     */
    public function toggle(CleaningSession $session, Room $room, Task $task)
    {
        $this->assertRoomOnSessionProperty($room, $session);
        $this->assertTaskAttachedToRoom($task, $room);

        // Create if missing (unique on session_id+room_id+task_id is recommended at DB level)
        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => $room->id,
                'task_id'    => $task->id,
            ],
            [
                'user_id' => auth()->id(),
                'checked' => false,
            ]
        );

        $nowChecked = ! $item->checked;

        $item->update([
            'checked'    => $nowChecked,
            'checked_at' => $nowChecked ? now() : null,
            'user_id'    => auth()->id(),
        ]);

        return back();
    }

    /**
     * Add/update a note for a specific (session, room, task).
     */
    public function note(Request $request, CleaningSession $session, Room $room, Task $task)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->assertRoomOnSessionProperty($room, $session);
        $this->assertTaskAttachedToRoom($task, $room);

        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => $room->id,
                'task_id'    => $task->id,
            ],
            [
                'user_id' => auth()->id(),
                'checked' => false,
            ]
        );

        $item->update([
            'note'    => $data['note'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back();
    }

    // ---------------------------
    // Guards for new relationships
    // ---------------------------

    /** Ensure the given room is attached to the session's property (property_room pivot). */
    private function assertRoomOnSessionProperty(Room $room, CleaningSession $session): void
    {
        $propertyId = $session->property_id;

        abort_unless(
            $room->properties()->where('properties.id', $propertyId)->exists(),
            404
        );
    }

    /** Ensure the given task is attached to the given room (room_task pivot). */
    private function assertTaskAttachedToRoom(Task $task, Room $room): void
    {
        abort_unless(
            $room->tasks()->where('tasks.id', $task->id)->exists(),
            404
        );
    }
}
