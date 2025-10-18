<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use App\Models\CleaningSession;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomPhoto;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BulkDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Configurable sizes (override via .env) ----
        $ownerCount        = (int) env('SEED_OWNER_COUNT', 5);
        $housekeeperCount  = (int) env('SEED_HK_COUNT', 12);
        $propertyCount     = (int) env('SEED_PROPERTY_COUNT', 120); // 100+ as requested
        $roomsPerProperty  = [4, 6];  // min, max (unique names)
        $tasksPerRoom      = [4, 6];  // for 'room' type (unique labels per pool)
        $inventoryPerProp  = [3, 5];  // number of 'inventory' tasks
        $sessionsPerProp   = [4, 8];  // sessions per property

        // ---- Ensure roles exist (Spatie) ----
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'housekeeper']);

        // ---- Create baseline users if missing ----
        if (!User::role('admin')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make(env('DEMO_USER_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ])->assignRole('admin');
        }

        // Create owners & housekeepers
        $owners = User::role('owner')->take($ownerCount)->get();
        if ($owners->count() < $ownerCount) {
            $owners = $owners->concat(
                User::factory()->count($ownerCount - $owners->count())->create()
                    ->each(fn($u) => $u->assignRole('owner'))
            );
        }

        $housekeepers = User::role('housekeeper')->take($housekeeperCount)->get();
        if ($housekeepers->count() < $housekeeperCount) {
            $housekeepers = $housekeepers->concat(
                User::factory()->count($housekeeperCount - $housekeepers->count())->create()
                    ->each(fn($u) => $u->assignRole('housekeeper'))
            );
        }

        // ---- Seed properties, rooms, tasks ----
        $allProperties = collect();
        $roomNamesPool = ['Bedroom', 'Kitchen', 'Bathroom', 'Living Room', 'Dining']; // unique names

        for ($i = 0; $i < $propertyCount; $i++) {
            $ownerUser = $owners->random();

            /** @var Property $property */
            $property = Property::factory()->create([
                'owner_id' => $ownerUser->id,
            ]);

            // Create rooms (cap requested to pool size and normalize to array)
            $requestedRooms = fake()->numberBetween(...$roomsPerProperty);
            $roomCount      = min($requestedRooms, count($roomNamesPool));
            $pickedRooms    = Arr::random($roomNamesPool, $roomCount);
            $pickedRooms    = is_array($pickedRooms) ? $pickedRooms : [$pickedRooms];

            $rooms = collect($pickedRooms)->map(fn($name) => Room::create([
                'property_id' => $property->id,
                'name'        => $name,
                'is_default'  => false,
            ]));

            // Add a dedicated 'Inventory' room for inventory tasks
            $inventoryRoom = Room::create([
                'property_id' => $property->id,
                'name'        => 'Inventory',
                'is_default'  => false,
            ]);

            // Tasks for each normal room (cap to pool size, normalize array)
            $taskPool = ['Sweep floor', 'Mop floor', 'Dust surfaces', 'Empty trash', 'Make bed', 'Wipe counters', 'Clean mirror'];
            foreach ($rooms as $room) {
                $requestedTasks = fake()->numberBetween(...$tasksPerRoom);
                $taskCount      = min($requestedTasks, count($taskPool));
                $labels         = Arr::random($taskPool, $taskCount);
                $labels         = is_array($labels) ? $labels : [$labels];

                foreach ($labels as $label) {
                    Task::create([
                        'property_id' => $property->id,
                        'room_id'     => $room->id,
                        'name'        => $label,
                        'is_default'  => false,
                        'type'        => 'room',
                    ]);
                }
            }

            // Inventory tasks (cap to pool size, normalize array)
            $invPool  = ['Restock soap', 'Restock toilet paper', 'Restock coffee/tea', 'Replace towels', 'Refill shampoo'];
            $reqInv   = fake()->numberBetween(...$inventoryPerProp);
            $invCount = min($reqInv, count($invPool));
            $invNames = Arr::random($invPool, $invCount);
            $invNames = is_array($invNames) ? $invNames : [$invNames];

            foreach ($invNames as $label) {
                Task::create([
                    'property_id' => $property->id,
                    'room_id'     => $inventoryRoom->id,
                    'name'        => $label,
                    'is_default'  => false,
                    'type'        => 'inventory',
                ]);
            }

            $allProperties->push($property);
        }

        // ---- Seed sessions + checklist + photos ----
        $usedKey       = []; // unique (hk, property, date)
        $statusOptions = ['pending', 'in_progress', 'completed'];

        foreach ($allProperties as $property) {
            $sessionCount = fake()->numberBetween(...$sessionsPerProp);

            for ($j = 0; $j < $sessionCount; $j++) {
                $hkUser = $housekeepers->random();
                $date   = fake()->dateTimeBetween('-10 days', '+20 days')->format('Y-m-d');
                $key    = $hkUser->id . '-' . $property->id . '-' . $date;

                if (isset($usedKey[$key])) {
                    $j--; // keep unique (retry)
                    continue;
                }
                $usedKey[$key] = true;

                $status  = Arr::random($statusOptions); // single value
                $session = CleaningSession::factory()->create([
                    'property_id'      => $property->id,
                    'owner_id'         => $property->owner_id,
                    'housekeeper_id'   => $hkUser->id,
                    'scheduled_date'   => $date,
                    'status'           => $status,
                    'start_latitude'   => $property->latitude ? ($property->latitude + fake()->randomFloat(6, -0.0008, 0.0008)) : null,
                    'start_longitude'  => $property->longitude ? ($property->longitude + fake()->randomFloat(6, -0.0008, 0.0008)) : null,
                ]);

                // Checklist items for all tasks in this property
                $rooms = Room::where('property_id', $property->id)->get();
                $tasks = Task::whereIn('room_id', $rooms->pluck('id'))->get();

                foreach ($tasks as $task) {
                    ChecklistItem::factory()->create([
                        'session_id' => $session->id,
                        'room_id'    => $task->room_id,
                        'task_id'    => $task->id,
                        'user_id'    => $hkUser->id,
                        'checked'    => $status === 'completed' ? true : fake()->boolean(60),
                        'checked_at' => $status === 'completed' ? now() : (fake()->boolean(50) ? now() : null),
                        'note'       => fake()->optional(0.15)->sentence(10),
                    ]);
                }

                // Photos: ≥8 per room when completed; fewer if not completed
                $minPhotos = $status === 'completed' ? 8 : fake()->numberBetween(2, 6);
                foreach ($rooms as $room) {
                    for ($k = 0; $k < $minPhotos; $k++) {
                        RoomPhoto::factory()->create([
                            'session_id'  => $session->id,
                            'room_id'     => $room->id,
                            'captured_at' => now()->subMinutes(fake()->numberBetween(0, 180)),
                        ]);
                    }
                }
            }
        }
    }
}
