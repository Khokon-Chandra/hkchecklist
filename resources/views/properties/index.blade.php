<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Properties</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <form method="get" class="flex gap-2">
                <x-form.input name="q" type="text" :value="old('q', request('q'))" autofocus autocomplete="name"
                    placeholder="Search by name" />
                <x-button variant="secondary">Filter</x-button>
                <x-button variant="secondary" :href="route('properties.index')">Clear</x-button>
            </form>

            @role('admin|owner')
                <x-button href="{{ route('properties.create') }}"
                    class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 text-white">+ New</x-button>
            @endrole

        </div>

        <x-card class="!px-0">
            <table class="min-w-full text-sm">
                <thead class="dark:bg-dark-eval-1">
                    <tr class="uppercase text-left">
                        <th class="px-4">Name</th>
                        <th class="px-4">Owner</th>
                        <th class="px-4">Beds</th>
                        <th class="px-4">Baths</th>
                        <th class="px-4">Rooms Count</th>
                        <th class="px-4">Lat/Lng</th>
                        <th class="px-4">Radius (m)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($properties as $p)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                            <td class="py-2 font-medium">{{ $p->owner->name }}</td>
                            <td class="px-4 py-2">{{ $p->beds }}</td>
                            <td class="px-4 py-2">{{ $p->baths }}</td>
                            <td class="px-4 py-2">{{ $p->rooms_count }}</td>
                            <td class="px-4 py-2">{{ $p->latitude }}, {{ $p->longitude }}</td>
                            <td class="px-4 py-2">{{ $p->geo_radius_m }}</td>
                            <td class="px-4 py-2 text-right">
                                @role('admin|owner')
                                    <a class="text-indigo-600 hover:underline"
                                        href="{{ route('properties.edit', $p) }}">Edit</a>
                                    <span class="mx-2">·</span>
                                @endrole
                                <a class="text-blue-600 hover:underline"
                                    href="{{ route('rooms.index', ['property' => $p->id]) }}">Rooms</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-500" colspan="6">No properties yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        {{ $properties->links() }}
    </div>
</x-app-layout>
