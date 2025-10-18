<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\CleaningSession;
use App\Models\Property;
use App\Models\User;

class ManageSessionController extends Controller
{

    public function index(Request $request)
    {
        $u = Auth::user();

        $filters = [
            'property_id'    => $request->integer('property_id') ?: null,
            'housekeeper_id' => $request->integer('housekeeper_id') ?: null,
            'status'         => $request->string('status')->toString() ?: null,
            'date_from'      => $request->date('date_from') ?: null,
            'date_to'        => $request->date('date_to') ?: null,
        ];

        $q = CleaningSession::query()
            ->with(['property:id,name,owner_id', 'property.rooms:id,property_id', 'housekeeper:id,name'])
            ->when(
                $u->hasRole('owner'),
                fn($qry) => $qry->whereHas('property', fn($p) => $p->where('owner_id', $u->id))
            )
            ->when($filters['property_id'], fn($qry, $v) => $qry->where('property_id', $v))
            ->when($filters['housekeeper_id'], fn($qry, $v) => $qry->where('housekeeper_id', $v))
            ->when($filters['status'], fn($qry, $v) => $qry->where('status', $v))
            ->when($filters['date_from'], fn($qry, $v) => $qry->whereDate('scheduled_date', '>=', $v))
            ->when($filters['date_to'], fn($qry, $v) => $qry->whereDate('scheduled_date', '<=', $v))
            ->orderByDesc('scheduled_date');

        $sessions = $q->paginate(20)->withQueryString();

        $properties = Property::query()
            ->when($u->hasRole('owner'), fn($qry) => $qry->where('owner_id', $u->id))
            ->orderBy('name')->get(['id', 'name']);

        $housekeepers = User::role('housekeeper')->orderBy('name')->get(['id', 'name']);

        return view('sessions.manage.index', compact('sessions', 'properties', 'housekeepers', 'filters'));
    }

    public function create()
    {
        $u = Auth::user();

        $properties = Property::query()
            ->when($u->hasRole('owner'), fn($qry) => $qry->where('owner_id', $u->id))
            ->orderBy('name')->get(['id', 'name']);

        $housekeepers = User::role('housekeeper')->orderBy('name')->get(['id', 'name']);

        return view('sessions.manage.create', compact('properties', 'housekeepers'));
    }

    public function store(Request $request)
    {
        $u = Auth::user();

        $data = $request->validate([
            'property_id'    => ['required', 'integer', 'exists:properties,id'],
            'housekeeper_id' => ['required', 'integer', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'status'         => [Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        // owner can only schedule for own properties
        if ($u->hasRole('owner')) {
            abort_unless(Property::where('id', $data['property_id'])->where('owner_id', $u->id)->exists(), 403);
        }
        // assignee must be housekeeper
        abort_unless(User::where('id', $data['housekeeper_id'])->role('housekeeper')->exists(), 422);

        // friendly duplicate check (migration also enforces unique)
        $dup = CleaningSession::where('property_id', $data['property_id'])
            ->where('housekeeper_id', $data['housekeeper_id'])
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->exists();
        if ($dup) {
            return back()->withErrors(['scheduled_date' => 'Duplicate assignment for this housekeeper/property/date.'])->withInput();
        }

        CleaningSession::create([
            'property_id'    => $data['property_id'],
            'owner_id'       => $u->hasRole('admin')
                ? Property::find($data['property_id'])->owner_id
                : $u->id,
            'housekeeper_id' => $data['housekeeper_id'],
            'scheduled_date' => $data['scheduled_date'],
            'status'         => $data['status'] ?? 'pending',
        ]);

        return redirect()->route('manage.sessions.index')->with('ok', 'Assignment created.');
    }

    public function edit(CleaningSession $session)
    {
        $u = Auth::user();
        if ($u->hasRole('owner')) {
            abort_unless($session->property->owner_id === $u->id, 403);
        }

        $properties = Property::query()
            ->when($u->hasRole('owner'), fn($qry) => $qry->where('owner_id', $u->id))
            ->orderBy('name')->get(['id', 'name']);

        $housekeepers = User::role('housekeeper')->orderBy('name')->get(['id', 'name']);

        return view('sessions.manage.edit', compact('session', 'properties', 'housekeepers'));
    }

    public function update(Request $request, CleaningSession $session)
    {
        $u = Auth::user();
        if ($u->hasRole('owner')) {
            abort_unless($session->property->owner_id === $u->id, 403);
        }

        $data = $request->validate([
            'property_id'    => ['required', 'integer', 'exists:properties,id'],
            'housekeeper_id' => ['required', 'integer', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'status'         => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ]);

        if ($u->hasRole('owner')) {
            abort_unless(Property::where('id', $data['property_id'])->where('owner_id', $u->id)->exists(), 403);
        }
        abort_unless(User::where('id', $data['housekeeper_id'])->role('housekeeper')->exists(), 422);

        $dup = CleaningSession::where('property_id', $data['property_id'])
            ->where('housekeeper_id', $data['housekeeper_id'])
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->where('id', '<>', $session->id)
            ->exists();
        if ($dup) {
            return back()->withErrors(['scheduled_date' => 'Duplicate assignment for this housekeeper/property/date.'])->withInput();
        }

        $session->update($data);

        return redirect()->route('manage.sessions.index')->with('ok', 'Assignment updated.');
    }

    public function destroy(CleaningSession $session)
    {
        $u = Auth::user();
        if ($u->hasRole('owner')) {
            abort_unless($session->property->owner_id === $u->id, 403);
        }

        $session->delete();

        return redirect()->route('manage.sessions.index')->with('ok', 'Assignment deleted.');
    }
}
