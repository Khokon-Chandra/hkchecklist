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
        // Order rooms & tasks by their pivot sort_order (no visual design change, just consistency)
        $rooms = $session->property->rooms()
            ->with([
                'tasks' => fn($q) => $q->orderBy('room_task.sort_order')->orderBy('tasks.name'),
                'tasks.media',
            ])
            ->orderBy('property_room.sort_order')
            ->get();


        // Ensure existing checklist items (same as before, but already correct with room context)
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

        // Eager-load checklist items to avoid N+1 when the view scans them
        $session->load('checklistItems');

        // Load property-level tasks
        $property = $session->property;
        $propertyTasks = $property->propertyTasks()
            ->orderBy('property_tasks.sort_order')
            ->get();

        // Ensure property-level checklist items exist
        foreach ($propertyTasks as $task) {
            \App\Models\ChecklistItem::firstOrCreate(
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
        }

        // Separate property-level tasks by phase
        $preCleaningTasks = $propertyTasks->where('phase', 'pre_cleaning');
        $duringCleaningTasks = $propertyTasks->where('phase', 'during_cleaning');
        $postCleaningTasks = $propertyTasks->where('phase', 'post_cleaning');

        // Count property-level tasks
        $preCleaningCount = $preCleaningTasks->count();
        $duringCleaningCount = $duringCleaningTasks->count();
        $postCleaningCount = $postCleaningTasks->count();

        // Count checked property-level tasks
        $checkedPreCleaningCount = ChecklistItem::where('session_id', $session->id)
            ->whereNull('room_id')
            ->whereIn('task_id', $preCleaningTasks->pluck('id'))
            ->where('checked', true)
            ->count();
        $checkedDuringCleaningCount = ChecklistItem::where('session_id', $session->id)
            ->whereNull('room_id')
            ->whereIn('task_id', $duringCleaningTasks->pluck('id'))
            ->where('checked', true)
            ->count();
        $checkedPostCleaningCount = ChecklistItem::where('session_id', $session->id)
            ->whereNull('room_id')
            ->whereIn('task_id', $postCleaningTasks->pluck('id'))
            ->where('checked', true)
            ->count();

        // Separate tasks by type and find first incomplete room indices (unchanged)
        $roomTasksByRoom      = [];
        $inventoryTasksByRoom = [];
        $firstIncompleteRoomIndex      = null;
        $firstIncompleteInventoryIndex = null;

        foreach ($rooms as $index => $room) {
            $roomTasks      = $room->tasks->where('type', 'room');
            $inventoryTasks = $room->tasks->where('type', 'inventory');
            $roomTasksByRoom[$room->id]      = $roomTasks;
            $inventoryTasksByRoom[$room->id] = $inventoryTasks;

            $checkedCount = ChecklistItem::where('session_id', $session->id)
                ->where('room_id', $room->id)
                ->whereIn('task_id', $roomTasks->pluck('id'))
                ->where('checked', true)
                ->count();
            $totalCount = $roomTasks->count();
            if ($firstIncompleteRoomIndex === null && $checkedCount < $totalCount) {
                $firstIncompleteRoomIndex = $index;
            }

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

        // Counts & stages (updated to include property-level tasks)
        $allRoomTasksCount          = $rooms->flatMap->tasks->where('type', 'room')->count();
        $checkedRoomTasksCount      = ChecklistItem::where('session_id', $session->id)
            ->whereHas('task', fn($q) => $q->where('type', 'room'))
            ->whereNotNull('room_id')
            ->where('checked', true)
            ->count();
        $allInventoryTasksCount     = $rooms->flatMap->tasks->where('type', 'inventory')->count();
        $checkedInventoryTasksCount = ChecklistItem::where('session_id', $session->id)
            ->whereHas('task', fn($q) => $q->where('type', 'inventory'))
            ->whereNotNull('room_id')
            ->where('checked', true)
            ->count();

        $photoCounts = $session->photos()
            ->selectRaw('room_id, count(*) as c')
            ->groupBy('room_id')
            ->pluck('c', 'room_id');
        $hasMinPhotos = $rooms->every(fn($room) => ($photoCounts[$room->id] ?? 0) >= 8);

        // Determine current stage based on completion status
        // Flow: pre_cleaning → rooms → during_cleaning → post_cleaning → inventory → photos
        if ($session->status === 'completed') {
            $stage = 'summary';
        } else {
            // Check pre-cleaning tasks first
            if ($preCleaningCount > 0 && $checkedPreCleaningCount < $preCleaningCount) {
                $stage = 'pre_cleaning';
            }
            // Then room tasks
            elseif ($allRoomTasksCount > 0 && $checkedRoomTasksCount < $allRoomTasksCount) {
                $stage = 'rooms';
            }
            // Then during-cleaning tasks
            elseif ($duringCleaningCount > 0 && $checkedDuringCleaningCount < $duringCleaningCount) {
                $stage = 'during_cleaning';
            }
            // Then post-cleaning tasks
            elseif ($postCleaningCount > 0 && $checkedPostCleaningCount < $postCleaningCount) {
                $stage = 'post_cleaning';
            }
            // Then inventory tasks
            elseif ($allInventoryTasksCount > 0 && $checkedInventoryTasksCount < $allInventoryTasksCount) {
                $stage = 'inventory';
            }
            // Finally photos
            else {
                $stage = 'photos';
            }
        }

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
            'photosByRoom',
            'preCleaningTasks',
            'duringCleaningTasks',
            'postCleaningTasks',
            'preCleaningCount',
            'duringCleaningCount',
            'postCleaningCount',
            'checkedPreCleaningCount',
            'checkedDuringCleaningCount',
            'checkedPostCleaningCount'
        ));
    }





    public function start(StartSessionRequest $request, CleaningSession $session)
    {
        $lat = (float) $request->validated('latitude');
        $lng = (float) $request->validated('longitude');

        $property = $session->property;

        // Geofence (only if property has coordinates)
        if ($property->latitude !== null && $property->longitude !== null) {
            $distance = GpsService::distanceMeters(
                $lat,
                $lng,
                (float) $property->latitude,
                (float) $property->longitude
            );

            if ($distance > (float) $property->geo_radius_m) {
                // If you want to hard-block, uncomment:
                // return back()->withErrors(['gps' => 'You are too far from the property to start.']);
            }
        }

        $session->update([
            'status'           => 'in_progress',
            'started_at'       => now(),
            'gps_confirmed_at' => now(),
            'start_latitude'   => $lat,
            'start_longitude'  => $lng,
        ]);

        activity()->performedOn($session)->event('started')->log('Session started');

        // ---- Bootstrap checklist items from Property -> Rooms (pivot) -> Tasks (pivot)
        // We must use the room from the property-room pivot, then each task attached to that room.
        $rooms = $property->rooms()
            ->with(['tasks' => function ($q) {
                // Keep your preferred ordering
                $q->orderBy('room_task.sort_order')->orderBy('tasks.name');
            }])
            ->orderBy('property_room.sort_order')
            ->get();

        // Load property-level tasks
        $propertyTasks = $property->propertyTasks()
            ->orderBy('property_tasks.sort_order')
            ->get();

        // Build rows for bulk insert; avoid per-row queries
        $rows = [];
        $now  = now();
        $uid  = auth()->id();

        // Add room-level tasks
        foreach ($rooms as $room) {
            foreach ($room->tasks as $task) {
                $rows[] = [
                    'session_id' => $session->id,
                    'room_id'    => $room->id,   // << from the property-room context
                    'task_id'    => $task->id,
                    'user_id'    => $uid,
                    'checked'    => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Add property-level tasks (room_id is null)
        foreach ($propertyTasks as $task) {
            $rows[] = [
                'session_id' => $session->id,
                'room_id'    => null,  // Property-level tasks have no room
                'task_id'    => $task->id,
                'user_id'    => $uid,
                'checked'    => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            // Insert only those that don't already exist (unique by session_id+room_id+task_id)
            // If you have a DB unique index on these three, insertOrIgnore is perfect.
            ChecklistItem::insertOrIgnore($rows);
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
