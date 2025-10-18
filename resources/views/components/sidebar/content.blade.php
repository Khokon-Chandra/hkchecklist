<x-perfect-scrollbar as="nav" aria-label="main" class="flex flex-col flex-1 gap-4 px-3">

    <x-sidebar.link title="Dashboard" href="{{ route('dashboard') }}" :isActive="request()->routeIs('dashboard')">
        <x-slot name="icon">
            <x-icons.dashboard class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>

    <x-sidebar.dropdown title="Properties" :active="Str::startsWith(request()->route()->uri('properties'), 'properties')">
        <x-slot name="icon">
            <x-heroicon-o-home class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>

        <x-sidebar.sublink title="All properties" href="{{ route('properties.index') }}" :active="request()->routeIs('properties.index')" />
        <x-sidebar.sublink title="Add new" href="{{ route('properties.create') }}" :active="request()->routeIs('properties.create')" />
    </x-sidebar.dropdown>



    <x-sidebar.link title="Users" href="{{ route('users.index') }}" :isActive="request()->routeIs('users.index')">
        <x-slot name="icon">
            <x-heroicon-o-users class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        </x-slot>
    </x-sidebar.link>



</x-perfect-scrollbar>
