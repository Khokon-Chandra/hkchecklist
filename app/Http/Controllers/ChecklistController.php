<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistNoteRequest;
use App\Http\Requests\ChecklistToggleRequest;
use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use App\Models\Room;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    /**
     * Toggle the checked state of a checklist item for a specific (session, room, task).
     * Requires: the room belongs to the session's property AND the task is attached to that room.
     */
    public function toggle(ChecklistToggleRequest $request, CleaningSession $session, Room $room, Task $task)
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

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'checked' => $nowChecked,
                'item'    => [
                    'id'         => $item->id,
                    'checked'    => $item->checked,
                    'checked_at' => $item->checked_at?->toIso8601String(),
                ],
            ]);
        }

        return back();
    }

    /**
     * Toggle property-level task (no room).
     */
    public function togglePropertyTask(ChecklistToggleRequest $request, CleaningSession $session, Task $task)
    {
        // Verify task is a property-level task for this session's property
        abort_unless(
            $session->property->propertyTasks()->where('tasks.id', $task->id)->exists(),
            404,
            'Task not found as property-level task for this session.'
        );

        // Create if missing
        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => null, // Property-level tasks have no room
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

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'checked' => $nowChecked,
                'item'    => [
                    'id'         => $item->id,
                    'checked'    => $item->checked,
                    'checked_at' => $item->checked_at?->toIso8601String(),
                ],
            ]);
        }

        return back();
    }

    /**
     * Add/update a note for a specific (session, room, task).
     */
    public function note(ChecklistNoteRequest $request, CleaningSession $session, Room $room, Task $task)
    {
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
            'note'    => $request->validated('note'),
            'user_id' => auth()->id(),
        ]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note saved successfully',
                'item'    => [
                    'id'   => $item->id,
                    'note' => $item->note,
                ],
            ]);
        }

        return back();
    }

    /**
     * Add/update a note for property-level task (no room).
     */
    public function notePropertyTask(ChecklistNoteRequest $request, CleaningSession $session, Task $task)
    {
        // Verify task is a property-level task for this session's property
        abort_unless(
            $session->property->propertyTasks()->where('tasks.id', $task->id)->exists(),
            404,
            'Task not found as property-level task for this session.'
        );

        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => null, // Property-level tasks have no room
                'task_id'    => $task->id,
            ],
            [
                'user_id' => auth()->id(),
                'checked' => false,
            ]
        );

        $item->update([
            'note'    => $request->validated('note'),
            'user_id' => auth()->id(),
        ]);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note saved successfully',
                'item'    => [
                    'id'   => $item->id,
                    'note' => $item->note,
                ],
            ]);
        }

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
