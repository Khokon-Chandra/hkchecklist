<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    /**
     * Toggle the checked state of a checklist item.  If the item doesn’t exist yet it
     * will be created for the given session/task/room combination.
     */
    public function toggle(CleaningSession $session, Task $task)
    {
        // Optional safety: ensure the task belongs to the session’s property
        if ($task->room && $task->room->property_id !== $session->property_id) {
            abort(404);
        }

        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => $task->room_id,
                'task_id'    => $task->id,
            ],
            [
                'user_id' => auth()->id(),
                'checked' => false,
            ]
        );

        $item->update([
            'checked'    => ! $item->checked,
            'checked_at' => now(),
            'user_id'    => auth()->id(),
        ]);

        return back();
    }

    /**
     * Add or update a note on a checklist item.  Creates the item if it doesn’t already exist.
     */
    public function note(Request $request, CleaningSession $session, Task $task)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($task->room && $task->room->property_id !== $session->property_id) {
            abort(404);
        }

        $item = ChecklistItem::firstOrCreate(
            [
                'session_id' => $session->id,
                'room_id'    => $task->room_id,
                'task_id'    => $task->id,
            ],
            [
                'user_id' => auth()->id(),
                'checked' => false,
            ]
        );

        $item->update(['note' => $data['note'] ?? null]);

        return back();
    }
}
