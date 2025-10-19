<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $this->assertOwnerOrAdmin();

        $auth = $request->user();
        $role = (string) $request->query('role', '');
        $q    = (string) $request->query('q', '');

        $users = User::query()
            ->with('roles')
            ->when($auth->hasRole('owner') && ! $auth->hasRole('admin'), function ($qry) use ($auth) {
                $qry->where(function ($q) use ($auth) {
                    $q->where('users.id', $auth->id)
                        ->orWhere(function ($qq) use ($auth) {
                            $qq->whereHas('roles', fn($r) => $r->where('name', 'housekeeper'))
                                ->whereExists(function ($sub) use ($auth) {
                                    $sub->selectRaw(1)
                                        ->from('cleaning_sessions as cs')
                                        ->whereColumn('cs.housekeeper_id', 'users.id')
                                        ->where('cs.owner_id', $auth->id);
                                });
                        });
                });
            })
            ->when($q !== '', fn($qry) => $qry->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->when($role !== '', fn($qry) => $qry->whereHas('roles', fn($r) => $r->where('name', $role)))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function assignRole(Request $request, User $user)
    {
        $this->assertOwnerOrAdmin();

        $auth = $request->user();

        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'owner', 'housekeeper'])],
        ]);

        // Admin can assign anything.
        if ($auth->hasRole('owner') && ! $auth->hasRole('admin')) {
            // Owners may ONLY assign housekeeper role.
            if ($data['role'] !== 'housekeeper') {
                abort(403, 'Owners can only assign the housekeeper role.');
            }
            // Owners cannot modify privileged users.
            if ($user->hasAnyRole(['admin', 'owner'])) {
                abort(403, 'Cannot modify admin/owner users.');
            }
            // If you want to restrict to their own HKs only, enforce exists() below:
            $isAssignedToOwner = DB::table('cleaning_sessions')
                ->where('owner_id', $auth->id)
                ->where('housekeeper_id', $user->id)
                ->exists();
            abort_unless($isAssignedToOwner, 403, 'Not your housekeeper.');
        }

        // If you want single-role model: use syncRoles([$data['role']]);
        $user->assignRole($data['role']);

        return back()->with('ok', 'Role assigned.');
    }

    private function assertOwnerOrAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->hasAnyRole(['admin', 'owner']), 403);
    }
}
