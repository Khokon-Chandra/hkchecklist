<x-perfect-scrollbar as="nav" aria-label="main" class="flex flex-col flex-1 gap-4 px-3">

    {{-- Primary --}}
    <x-sidebar.link title="Dashboard"
        href="{{ route('dashboard') }}"
        :isActive="request()->routeIs('dashboard') || request()->routeIs('welcome')">
        <x-slot name="icon"><x-icons.dashboard class="w-6 h-6" aria-hidden="true" /></x-slot>
    </x-sidebar.link>

    <x-sidebar.link title="Calendar"
        href="{{ route('calendar.index') }}"
        :isActive="request()->routeIs('calendar.index')">
        <x-slot name="icon"><x-heroicon-o-calendar class="w-6 h-6" aria-hidden="true" /></x-slot>
    </x-sidebar.link>

    {{-- Properties --}}
    <x-sidebar.dropdown title="Properties"
        :active="request()->routeIs('properties.*')">
        <x-slot name="icon"><x-heroicon-o-home class="w-6 h-6" aria-hidden="true" /></x-slot>

        <x-sidebar.sublink title="All Properties"
            href="{{ route('properties.index') }}"
            :active="request()->routeIs('properties.index')" />

        @role('admin|owner')
            <x-sidebar.sublink title="Add New"
                href="{{ route('properties.create') }}"
                :active="request()->routeIs('properties.create')" />
        @endrole
    </x-sidebar.dropdown>

    {{-- Catalogs (Admin/Owner) --}}
    @role('admin|owner')
        <x-sidebar.link title="All Rooms"
            href="{{ route('rooms.index') }}"
            :isActive="request()->routeIs('rooms.*')">
            <x-slot name="icon"><x-icons.rooms class="w-6 h-6" aria-hidden="true" /></x-slot>
        </x-sidebar.link>

        <x-sidebar.link title="All Tasks"
            href="{{ route('tasks.index') }}"
            :isActive="request()->routeIs('tasks.*')">
            <x-slot name="icon"><x-icons.tasks class="w-6 h-6" aria-hidden="true" /></x-slot>
        </x-sidebar.link>
    @endrole

    {{-- Users (Admin/Owner) --}}
    @role('owner|admin')
        <x-sidebar.dropdown title="Users"
            :active="request()->routeIs('users.*')">
            <x-slot name="icon"><x-heroicon-o-users class="w-6 h-6" aria-hidden="true" /></x-slot>

            <x-sidebar.sublink title="Create User"
                href="{{ route('users.create') }}"
                :active="request()->routeIs('users.create')" />

            {{-- All (no role filter) --}}
            <x-sidebar.sublink title="All Users"
                href="{{ route('users.index') }}"
                :active="request()->routeIs('users.index') && !request()->filled('role')" />

            @role('admin')
                <x-sidebar.sublink title="Admins"
                    href="{{ route('users.index', ['role' => 'admin']) }}"
                    :active="request()->routeIs('users.index') && request('role') === 'admin'" />
            @endrole

            <x-sidebar.sublink title="Owners"
                href="{{ route('users.index', ['role' => 'owner']) }}"
                :active="request()->routeIs('users.index') && request('role') === 'owner'" />

            <x-sidebar.sublink title="Housekeepers"
                href="{{ route('users.index', ['role' => 'housekeeper']) }}"
                :active="request()->routeIs('users.index') && request('role') === 'housekeeper'" />
        </x-sidebar.dropdown>
    @endrole

    {{-- Assignments --}}
    @role('owner|admin')
        <x-sidebar.dropdown title="Assignments"
            :active="request()->routeIs('manage.sessions.*') || request()->routeIs('sessions.*')">
            <x-slot name="icon"><x-icons.assignment class="w-6 h-6" /></x-slot>

            <x-sidebar.sublink title="Manage Sessions"
                href="{{ route('manage.sessions.index') }}"
                :active="request()->routeIs('manage.sessions.index')" />

            <x-sidebar.sublink title="New Assignment"
                href="{{ route('manage.sessions.create') }}"
                :active="request()->routeIs('manage.sessions.create')" />

            {{-- Jump to HK list view --}}
            <x-sidebar.sublink title="My Assignments (HK View)"
                href="{{ route('sessions.index') }}"
                :active="request()->routeIs('sessions.*')" />
        </x-sidebar.dropdown>
    @endrole

    @role('housekeeper')
        <x-sidebar.link title="My Assignments"
            href="{{ route('sessions.index') }}"
            :isActive="request()->routeIs('sessions.*')">
            <x-slot name="icon"><x-icons.assignment class="w-6 h-6" aria-hidden="true" /></x-slot>
        </x-sidebar.link>
    @endrole

    {{-- System / Audit --}}
    @role('admin')
        <x-sidebar.link title="Activity Log"
            href="{{ route('activity.index') }}"
            :isActive="request()->routeIs('activity.*')">
            <x-slot name="icon"><x-icons.activity class="w-6 h-6" aria-hidden="true" /></x-slot>
        </x-sidebar.link>
    @endrole

</x-perfect-scrollbar>
