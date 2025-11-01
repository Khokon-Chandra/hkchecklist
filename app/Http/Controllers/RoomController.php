<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{

    public function index()
    {

        $rooms = Room::withCount('tasks')->latest()->paginate(20);

        return view('rooms.index', [
            'rooms'       => $rooms,
        ]);
    }

    public function create()
    {

        return view('rooms.create');
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $room = Room::create([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('rooms.index')->with('ok', 'Room added.');
    }

    public function edit(Room $room)
    {

        return view('rooms.edit', [
            'room'        => $room
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $room->update([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('rooms.index')->with('ok', 'Room updated.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('ok', 'Room deleted.');
    }
}
