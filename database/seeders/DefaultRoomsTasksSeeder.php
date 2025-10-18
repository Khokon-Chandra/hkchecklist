<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DefaultRoomsTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // System-wide defaults live without property_id, owners can copy to their property
        $defaults = [
            'Bedroom' => ['Make bed', 'Sweep floor', 'Dust surfaces', 'Empty trash'],
            'Kitchen' => ['Clean sink', 'Wipe counters', 'Mop floor', 'Check appliances'],
            'Bathroom' => ['Clean toilet', 'Clean sink', 'Clean shower', 'Replace towels'],
        ];
        foreach ($defaults as $room => $tasks) {
            $r = Room::firstOrCreate(['property_id' => null, 'name' => $room], ['is_default' => true]);
            foreach ($tasks as $t) {
                Task::firstOrCreate(['room_id' => $r->id, 'name' => $t], ['is_default' => true, 'type' => 'room']);
            }
        }

        // Inventory example
        $inv = ['Restock soap', 'Restock toilet paper', 'Restock coffee/tea'];
        $invRoom = Room::firstOrCreate(['property_id' => null, 'name' => 'Inventory'], ['is_default' => true]);
        foreach ($inv as $t) {
            Task::firstOrCreate(['room_id' => $invRoom->id, 'name' => $t], ['is_default' => true, 'type' => 'inventory']);
        }
    }
}
