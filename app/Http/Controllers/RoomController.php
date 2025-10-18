<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Properties\Models\Property;
use App\Domain\Rooms\Models\Room;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Property $property)
    {
        $this->authorize('view', $property);

        $rooms = $property->rooms()->orderBy('name')->paginate(20);

        return view('rooms.index', [
            'property'    => $property,
            'rooms'       => $rooms,
            'navProperty' => $property, // secondary nav context
        ]);
    }

    public function create(Property $property)
    {
        $this->authorize('update', $property);

        return view('rooms.create', [
            'property'    => $property,
            'navProperty' => $property,
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $property->rooms()->create([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('rooms.index', $property)->with('ok', 'Room added.');
    }

    public function edit(Property $property, Room $room)
    {
        $this->assertBelongs($room, $property);

        return view('rooms.edit', [
            'property'    => $property,
            'room'        => $room,
            'navProperty' => $property,
        ]);
    }

    public function update(Request $request, Property $property, Room $room)
    {
        $this->authorize('update', $property);
        $this->assertBelongs($room, $property);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $room->update([
            'name'       => $data['name'],
            'is_default' => (bool)($data['is_default'] ?? false),
        ]);

        return redirect()->route('rooms.index', $property)->with('ok', 'Room updated.');
    }

    public function destroy(Property $property, Room $room)
    {
        $this->assertBelongs($room, $property);

        $room->delete();

        return redirect()->route('rooms.index', $property)->with('ok', 'Room deleted.');
    }

    private function assertBelongs(Room $room, Property $property): void
    {
        abort_unless($room->property_id === $property->id, 404);
    }
}
