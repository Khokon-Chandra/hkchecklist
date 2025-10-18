<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartSessionRequest;
use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use App\Services\GpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = CleaningSession::query()
            ->where('housekeeper_id', Auth::id())
            ->whereDate('scheduled_date', '<=', now()->toDateString())
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return view('sessions.index', compact('sessions'));
    }

    public function show(CleaningSession $session)
    {
        $rooms = $session->property->rooms()->with(['tasks'])->get();

        // derive stage
        $hasAllRoomTasksDone = ChecklistItem::where('session_id', $session->id)
            ->whereHas('session.property.rooms.tasks', fn($q) => $q)
            ->where('checked', true)->count() >=
            $rooms->flatMap->tasks->count();

        $photoCounts = $session->photos()->selectRaw('room_id, count(*) as c')->groupBy('room_id')->pluck('c', 'room_id');
        $hasMinPhotos = $rooms->every(fn($room) => ($photoCounts[$room->id] ?? 0) >= 8);

        $stage = 'rooms';
        if ($hasAllRoomTasksDone) $stage = 'inventory';
        if ($hasAllRoomTasksDone && $this->inventoryCompleted($session)) $stage = 'photos';

        return view('sessions.show', compact('session', 'rooms', 'stage', 'photoCounts', 'hasMinPhotos'));
    }

    public function start(StartSessionRequest $request, CleaningSession $session)
    {
        $lat = (float)$request->validated('latitude');
        $lng = (float)$request->validated('longitude');

        $p = $session->property;
        $distance = GpsService::distanceMeters($lat, $lng, (float)$p->latitude, (float)$p->longitude);
        if ($distance > (float)$p->geo_radius_m) {
            return back()->withErrors(['gps' => 'You are too far from the property to start.']);
        }

        $session->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'gps_confirmed_at' => now(),
            'start_latitude' => $lat,
            'start_longitude' => $lng,
        ]);

        activity()->performedOn($session)->event('started')->log("Session started within {$distance}m");

        // bootstrap checklist items (room & inventory)
        $tasks = $p->rooms()->with('tasks')->get()->flatMap->tasks;
        foreach ($tasks as $task) {
            \App\Models\ChecklistItem::firstOrCreate([
                'session_id' => $session->id,
                'room_id' => $task->room_id,
                'task_id' => $task->id
            ], ['user_id' => auth()->id(), 'checked' => false]);
        }

        return redirect()->route('sessions.show', $session);
    }

    public function complete(Request $request, CleaningSession $session)
    {

        $rooms = $session->property->rooms()->with('tasks')->get();
        foreach ($rooms as $room) {
            $count = $session->photos()->where('room_id', $room->id)->count();
            if ($count < 8) {
                return back()->withErrors(['photos' => "Room {$room->name} needs at least 8 photos."]);
            }
        }

        $session->update(['status' => 'completed', 'ended_at' => now()]);
        activity()->performedOn($session)->event('completed')->log('Session completed');
        return redirect()->route('sessions.index')->with('ok', 'Checklist submitted.');
    }

    private function inventoryCompleted(CleaningSession $session): bool
    {
        return ChecklistItem::where('session_id', $session->id)
            ->whereHas('session', fn($q) => $q)
            ->whereHas('task', fn($q) => $q->where('type', 'inventory'))
            ->where('checked', true)->exists();
    }
}
