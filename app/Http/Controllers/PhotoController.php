<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Sessions\Models\CleaningSession;
use App\Domain\Sessions\Models\RoomPhoto;
use App\Services\ImageTimestampService;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(Request $request, CleaningSession $session, int $roomId)
    {
        $this->authorize('update', $session);
        $request->validate(['photos.*' => 'required|image|max:5120']);

        foreach ((array)$request->file('photos') as $upload) {
            $path = $upload->store("sessions/{$session->id}/rooms/{$roomId}", 'public');
            $abs = Storage::disk('public')->path($path);

            ImageTimestampService::overlay($abs, now());

            RoomPhoto::create([
                'session_id' => $session->id,
                'room_id' => $roomId,
                'path' => $path,
                'captured_at' => now(),
                'has_timestamp_overlay' => true
            ]);
        }
        return back()->with('ok', 'Photos uploaded.');
    }
}
