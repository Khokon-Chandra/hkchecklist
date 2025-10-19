<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{


    public function index(Request $request)
    {
        $user = $request->user();
        $q    = (string) $request->query('q', '');

        $query = Property::query();

        if ($user->hasRole('owner')) {
            $query->where('owner_id', $user->id);
        } elseif ($user->hasRole('housekeeper')) {
            $query->whereIn('id', function ($sub) use ($user) {
                $sub->select('property_id')
                    ->from('cleaning_sessions')
                    ->where('housekeeper_id', $user->id);
            });
        }

        $properties = $query
            ->when($q !== '', fn($qry) => $qry->where('name', 'like', "%{$q}%"))
            ->with(['owner'])
            ->withCount('rooms')
            ->orderBy('name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('properties.index', compact('properties'));
    }

    public function create()
    {

        return view('properties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'beds'         => ['nullable', 'integer', 'min:0'],
            'baths'        => ['nullable', 'integer', 'min:0'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50', 'max:10000'],
        ]);

        $property = Property::create(array_merge($data, [
            'owner_id' => Auth::id(),
        ]));

        return redirect()->route('properties.edit', $property)->with('ok', 'Property created.');
    }

    public function edit(Property $property)
    {

        return view('properties.edit', ['property' => $property]);
    }

    public function update(Request $request, Property $property)
    {

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'beds'         => ['nullable', 'integer', 'min:0'],
            'baths'        => ['nullable', 'integer', 'min:0'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50', 'max:10000'],
        ]);

        $property->update($data);

        return redirect()->route('properties.index')->with('ok', 'Property updated.');
    }

    public function destroy(Property $property)
    {

        $property->delete();

        return redirect()->route('properties.index')->with('ok', 'Property deleted.');
    }
}
