<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskMediaController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'media.*'    => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:20480'],
            'captions.*' => ['nullable', 'string', 'max:255'],
        ]);

        $start = (int) $task->media()->max('sort_order');

        foreach ($request->file('media', []) as $i => $file) {
            $path = $file->store('task-media', 'public');
            $mime = $file->getMimeType();
            $type = str_starts_with($mime, 'video') ? 'video' : 'image';

            $task->media()->create([
                'type'       => $type,
                'url'        => Storage::disk('public')->url($path),
                'thumbnail'  => $type === 'image' ? Storage::disk('public')->url($path) : null,
                'caption'    => $request->input("captions.$i"),
                'sort_order' => $start + $i + 1,
            ]);
        }

        return back()->with('status', 'Media uploaded');
    }

    public function destroy(Task $task, TaskMedia $media)
    {
        $relPath = str_replace(Storage::disk('public')->url(''), '', $media->url);
        Storage::disk('public')->delete($relPath);

        $media->delete();
        return back()->with('status', 'Media removed');
    }
}
