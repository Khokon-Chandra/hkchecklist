<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function toggle(CleaningSession $session, ChecklistItem $item)
    {
        $this->authorize('update', $session);
        abort_unless($item->session_id === $session->id, 404);

        $item->update([
            'checked' => !$item->checked,
            'checked_at' => now(),
            'user_id' => auth()->id()
        ]);

        return back();
    }

    public function note(CleaningSession $session, ChecklistItem $item, Request $request)
    {
        $this->authorize('update', $session);
        $request->validate(['note' => 'nullable|string|max:2000']);
        abort_unless($item->session_id === $session->id, 404);

        $item->update(['note' => $request->note]);
        return back();
    }
}
