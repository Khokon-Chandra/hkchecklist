<x-perfect-scrollbar as="nav" aria-label="main" class="flex flex-col flex-1 gap-4 px-3">

    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard') || request()->routeIs('welcome')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <x-sidebar.link title="Calendar" href="{{ route('calendar.index') }}" :isActive="request()->routeIs('calendar.index')">
        <x-slot name="icon">
            <x-heroicon-o-calendar class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <x-sidebar.dropdown title="Properties" :active="Str::startsWith(request()->route()->uri('properties'), 'properties')">
        <x-slot name="icon">
            <x-heroicon-o-home class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>

        <x-sidebar.sublink title="All properties" href="{{ route('properties.index') }}" :active="request()->routeIs('properties.index')" />

        @role('admin|owner')
            <x-sidebar.sublink title="Add new" href="{{ route('properties.create') }}" :active="request()->routeIs('properties.create')" />
        @endrole
    </x-sidebar.dropdown>


    @role('owner|admin')
        <x-sidebar.dropdown title="Users" :active="request()->routeIs('users.*')">
            <x-slot name="icon">
                <x-heroicon-o-users class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>

            {{-- All users: active only when no role filter --}}
            <x-sidebar.sublink title="All users" href="{{ route('users.index') }}" :active="request()->routeIs('users.index') && !request()->filled('role')" />

            {{-- Role filters: active when role matches --}}
            @role('admin')
                <x-sidebar.sublink title="Admins" href="{{ route('users.index', ['role' => 'admin']) }}" :active="request()->routeIs('users.index') && request('role') === 'admin'" />
            @endrole

            <x-sidebar.sublink title="Owners" href="{{ route('users.index', ['role' => 'owner']) }}" :active="request()->routeIs('users.index') && request('role') === 'owner'" />

            <x-sidebar.sublink title="Housekeepers" href="{{ route('users.index', ['role' => 'housekeeper']) }}"
                :active="request()->routeIs('users.index') && request('role') === 'housekeeper'" />
        </x-sidebar.dropdown>
    @endrole


    {{-- Owner/Admin: Sessions management --}}
    @role('owner|admin')
        <x-sidebar.dropdown title="Manage Assignment" :active="request()->routeIs('manage.sessions.*') || request()->routeIs('sessions.*')">
            <x-slot name="icon"><x-icons.assignment class="w-6 h-6" /></x-slot>

            <x-sidebar.sublink title="Manage Sessions" href="{{ route('manage.sessions.index') }}" :active="request()->routeIs('manage.sessions.index')" />

            <x-sidebar.sublink title="New Assignment" href="{{ route('manage.sessions.create') }}" :active="request()->routeIs('manage.sessions.create')" />

            {{-- Owner/Admin can also jump to the housekeeper view if desired --}}
            <x-sidebar.sublink title="My Assignments (HK view)" href="{{ route('sessions.index') }}" :active="request()->routeIs('sessions.*')" />
        </x-sidebar.dropdown>
    @endrole



    {{-- Housekeeper: only My Assignments --}}
    @role('housekeeper')
        <x-sidebar.link title="My Assignments" href="{{ route('sessions.index') }}" :isActive="request()->routeIs('sessions.*')">
            <x-slot name="icon">
                <x-icons.assignment class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </x-slot>
        </x-sidebar.link>
    @endrole


</x-perfect-scrollbar>
