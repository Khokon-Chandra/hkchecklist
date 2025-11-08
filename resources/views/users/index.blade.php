<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Users & Roles</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-4">
        <form method="get" class="flex gap-2">
            <x-form.select name="role">
                <option value="">All roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                <option value="owner" @selected(request('role') === 'owner')>Owner</option>
                <option value="housekeeper" @selected(request('role') === 'housekeeper')>Housekeeper</option>
            </x-form.select>
            <x-form.input id="name" name="q" type="text" class="block w-full" :value="old('q', request('q'))"
                placeholder="Search by name/email..." />
            <x-button variant="secondary">Filter</x-button>
            <x-button :href="route('users.index')" variant="secondary">Clear</x-button>
        </form>
    </div>

    <x-card class="mb-4">
        <table class="min-w-full text-sm">
            <thead class="uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Phone</th>
                    <th class="px-4 py-2">Roles</th>
                    <th class="px-4 py-2 w-48">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-2">{{ $u->email }}</td>
                        <td class="px-4 py-2">{{ $u->phone_number ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @foreach ($u->roles as $r)
                                <span
                                    class="px-2 py-0.5 mr-1 rounded font-medium text-xs bg-gray-200 dark:bg-gray-700">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        @if (auth()->id() !== $u->id)
                            <td class="px-4 py-2 text-right">
                                <form method="post" action="{{ route('users.assignRole', $u) }}"
                                    class="inline-flex gap-2">
                                    @csrf
                                    <x-form.select name="role" class="!border-0 !py-1">
                                        <option>--select role--</option>
                                        <option value="owner">Owner</option>
                                        <option value="housekeeper">Housekeeper</option>
                                        <option value="admin">Admin</option>
                                    </x-form.select>
                                    <x-button>Assign</x-button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>

    <div>{{ $users->links() }}</div>
</x-app-layout>
