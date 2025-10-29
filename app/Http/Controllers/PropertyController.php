<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


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

        return view('properties.create', [
            'owners' => User::role('owner')->pluck('name', 'id')->all()
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50'],
            'photo'        => ['nullable', 'image', 'max:5120'], // 5MB
        ];

        if ($isAdmin) {
            // Admin must pick an owner
            $rules['owner_id'] = ['required', Rule::exists('users', 'id')];
        }

        $data = $request->validate($rules);

        // Owner users always own their created properties
        if (!$isAdmin) {
            $data['owner_id'] = $user->id;
        }

        // Handle file upload
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }

        $property = Property::create($data);

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property created successfully.');
    }

    public function edit(Property $property)
    {

        return view('properties.edit', [
            'property' => $property,
            'owners' => User::role('owner')->pluck('name', 'id')->all()
        ]);
    }

    public function update(Request $request, Property $property)
    {

        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'geo_radius_m' => ['nullable', 'integer', 'min:50'],
            'photo'        => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];

        if ($isAdmin) {
            // Admin can reassign owner
            $rules['owner_id'] = ['required', Rule::exists('users', 'id')];
        } else {
            // Non-admin cannot set/override owner_id
            // If you’re on Laravel 12, you can prohibit it like this:
            $rules['owner_id'] = ['prohibited'];
        }

        $data = $request->validate($rules);

        if (!$isAdmin) {
            // Guardrail: keep property with the same owner (should also be in a Policy)
            if ($property->owner_id !== $user->id) {
                abort(403, 'You cannot modify properties you do not own.');
            }
            unset($data['owner_id']); // ensure not mass-assigned
        }

        // Remove existing photo if requested
        if ($request->boolean('remove_photo') && $property->photo_path) {
            Storage::disk('public')->delete($property->photo_path);
            $data['photo_path'] = null;
        }

        // Replace with newly uploaded photo
        if ($request->hasFile('photo')) {
            if ($property->photo_path) {
                Storage::disk('public')->delete($property->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('properties', 'public');
        }

        $property->update($data);

        return redirect()
            ->route('properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {

        $property->delete();

        return redirect()->route('properties.index')->with('ok', 'Property deleted.');
    }
}
