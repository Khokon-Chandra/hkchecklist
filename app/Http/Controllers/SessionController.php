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
        // Load all rooms with their tasks
        $rooms = $session->property->rooms()->with(['tasks'])->get();

        // Make sure every task has a ChecklistItem for this session, even if the task
        // was created after the session started.  Missing items default to unchecked.
        foreach ($rooms as $room) {
            foreach ($room->tasks as $task) {
                \App\Models\ChecklistItem::firstOrCreate(
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
            }
        }

        // Separate tasks by type and find first incomplete room indices
        $roomTasksByRoom      = [];
        $inventoryTasksByRoom = [];
        $firstIncompleteRoomIndex      = null;
        $firstIncompleteInventoryIndex = null;

        foreach ($rooms as $index => $room) {
            $roomTasks      = $room->tasks->where('type', 'room');
            $inventoryTasks = $room->tasks->where('type', 'inventory');
            $roomTasksByRoom[$room->id]      = $roomTasks;
            $inventoryTasksByRoom[$room->id] = $inventoryTasks;

            // Find the first room that still has room tasks unchecked
            $checkedCount = ChecklistItem::where('session_id', $session->id)
                ->where('room_id', $room->id)
                ->whereIn('task_id', $roomTasks->pluck('id'))
                ->where('checked', true)
                ->count();
            $totalCount = $roomTasks->count();
            if ($firstIncompleteRoomIndex === null && $checkedCount < $totalCount) {
                $firstIncompleteRoomIndex = $index;
            }

            // Find the first room that still has inventory tasks unchecked
            $checkedInventoryCount = ChecklistItem::where('session_id', $session->id)
                ->where('room_id', $room->id)
                ->whereIn('task_id', $inventoryTasks->pluck('id'))
                ->where('checked', true)
                ->count();
            $totalInventoryCount = $inventoryTasks->count();
            if ($firstIncompleteInventoryIndex === null && $checkedInventoryCount < $totalInventoryCount) {
                $firstIncompleteInventoryIndex = $index;
            }
        }

        // Count all tasks by type for stage determination
        $allRoomTasksCount      = $rooms->flatMap->tasks->where('type', 'room')->count();
        $checkedRoomTasksCount  = ChecklistItem::where('session_id', $session->id)
            ->whereHas('task', fn($q) => $q->where('type', 'room'))
            ->where('checked', true)
            ->count();
        $allInventoryTasksCount = $rooms->flatMap->tasks->where('type', 'inventory')->count();
        $checkedInventoryTasksCount = ChecklistItem::where('session_id', $session->id)
            ->whereHas('task', fn($q) => $q->where('type', 'inventory'))
            ->where('checked', true)
            ->count();

        // Photo counts and minimum requirement check
        $photoCounts = $session->photos()
            ->selectRaw('room_id, count(*) as c')
            ->groupBy('room_id')
            ->pluck('c', 'room_id');
        $hasMinPhotos = $rooms->every(fn($room) => ($photoCounts[$room->id] ?? 0) >= 8);

        // Determine the current stage of the checklist
        // If the session is already completed, show the summary.
        if ($session->status === 'completed') {
            $stage = 'summary';
        } else {
            // If there are no tasks at all (unlikely), default to photos stage.
            if ($allRoomTasksCount === 0 && $allInventoryTasksCount === 0) {
                $stage = 'photos';
            } else {
                // If not all room tasks are complete → rooms stage.
                if ($checkedRoomTasksCount < $allRoomTasksCount) {
                    $stage = 'rooms';
                } else {
                    // All room tasks are complete
                    // If no inventory tasks exist → skip inventory stage
                    if ($allInventoryTasksCount === 0) {
                        $stage = 'photos';
                    } elseif ($checkedInventoryTasksCount < $allInventoryTasksCount) {
                        // Inventory tasks exist but not all done
                        $stage = 'inventory';
                    } else {
                        // All inventory tasks are done
                        $stage = 'photos';
                    }
                }
            }
        }

        // Group photos by room for gallery display
        $photosByRoom = $session->photos()->latest()->get()->groupBy('room_id');

        return view('sessions.show', compact(
            'session',
            'rooms',
            'stage',
            'photoCounts',
            'hasMinPhotos',
            'roomTasksByRoom',
            'inventoryTasksByRoom',
            'firstIncompleteRoomIndex',
            'firstIncompleteInventoryIndex',
            'photosByRoom'
        ));
    }




    public function start(StartSessionRequest $request, CleaningSession $session)
    {
        $lat = (float)$request->validated('latitude');
        $lng = (float)$request->validated('longitude');

        $p = $session->property;
        $distance = GpsService::distanceMeters($lat, $lng, (float)$p->latitude, (float)$p->longitude);
        if ($distance > (float)$p->geo_radius_m) {
            // return back()->withErrors(['gps' => 'You are too far from the property to start.']);
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
        return redirect()->route('sessions.show', $session->id)->with('ok', 'Checklist submitted.');
    }
}
