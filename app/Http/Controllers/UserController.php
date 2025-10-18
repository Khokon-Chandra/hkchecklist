<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // $this->assertOwnerOrAdmin();

        $role = (string)$request->query('role', '');
        $q    = (string)$request->query('q', '');

        $users = User::query()
            ->with('roles')
            ->when($q !== '', fn($qry) => $qry->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
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

        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'owner', 'housekeeper'])],
        ]);

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
