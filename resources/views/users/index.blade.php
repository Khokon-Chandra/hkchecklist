<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Users & Roles</h2>
    </x-slot>

    <div class="mb-4">
        <form method="get" class="flex gap-2">
            <select name="role" class="rounded border-gray-300">
                <option value="">All roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                <option value="owner" @selected(request('role') === 'owner')>Owner</option>
                <option value="housekeeper" @selected(request('role') === 'housekeeper')>Housekeeper</option>
            </select>
            <input name="q" value="{{ request('q') }}" class="rounded border-gray-300"
                placeholder="Search name/email...">
            <x-primary-button>Filter</x-primary-button>
        </form>
    </div>

    <div class="overflow-hidden rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Phone</th>
                    <th class="px-4 py-2">Roles</th>
                    <th class="px-4 py-2 w-48"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-2">{{ $u->email }}</td>
                        <td class="px-4 py-2">{{ $u->phone_number ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @foreach ($u->roles as $r)
                                <span class="px-2 py-0.5 mr-1 rounded text-xs bg-gray-100">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-2 text-right">
                            @can('assignRoles', $u)
                                <form method="post" action="{{ route('users.assignRole', $u) }}" class="inline-flex gap-2">
                                    @csrf
                                    <select name="role" class="rounded border-gray-300">
                                        <option value="owner">Owner</option>
                                        <option value="housekeeper">Housekeeper</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <x-primary-button>Assign</x-primary-button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</x-app-layout>
