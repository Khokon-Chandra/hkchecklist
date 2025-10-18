<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{


    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $properties = Property::query()
            ->when($q !== '', fn($qry) => $qry->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
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
