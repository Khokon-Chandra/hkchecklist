<?php

namespace App\Http\Controllers;

use App\Models\CleaningSession;
use App\Models\RoomPhoto;
use App\Services\ImageTimestampService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(Request $request, CleaningSession $session, $roomId)
    {
        $request->validate([
            'photos.*' => ['required', 'image', 'max:5120'], // 5MB per image
        ]);

        $room   = $session->property->rooms()->findOrFail($roomId);
        $saved  = [];
        foreach ($request->file('photos', []) as $file) {
            $filename = $file->store('room_photos', 'public');
            $photo    = $session->photos()->create([
                'room_id'     => $room->id,
                'path'        => $filename,
                'captured_at' => now(),
                // optionally set has_timestamp_overlay = true if your service overlays it
            ]);
            $saved[] = [
                'id'          => $photo->id,
                'url'         => asset('storage/' . $filename),
                'captured_at' => $photo->captured_at->format('H:i'),
            ];
        }

        // For AJAX calls, return JSON. The front end can add these to the gallery without reloading.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => count($saved) . ' photos uploaded.',
                'photos'  => $saved,
            ]);
        }

        // Fallback to standard redirect if not an AJAX request
        return back()->with('ok', count($saved) . ' photos uploaded.');
    }
}
