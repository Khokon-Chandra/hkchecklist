<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Properties</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <form method="get" class="flex gap-2">
                <x-form.input id="name" name="q" type="text" class="block w-full" :value="old('q', request('q'))" autofocus
                    autocomplete="name" placeholder="Search by name" />
                <x-button variant="secondary">Filter</x-button>
            </form>

            <x-button href="{{ route('properties.create') }}"
                class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 text-white">+ New</x-button>

        </div>

        <div
            class="overflow-hidden rounded p-4 sm:p-8 bg-white shadow  text-gray-600 dark:text-gray-400 sm:rounded-lg dark:bg-gray-800">
            <table class="min-w-full text-sm">
                <thead class="dark:bg-dark-eval-1">
                    <tr class="uppercase text-left">
                        <th>Owner</th>
                        <th>Name</th>
                        <th>Beds</th>
                        <th>Baths</th>
                        <th>Rooms Count</th>
                        <th>Lat/Lng</th>
                        <th>Radius (m)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($properties as $p)
                        <tr>
                            <td class="py-2 font-medium">{{ $p->owner->name }}</td>
                            <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                            <td class="px-4 py-2">{{ $p->beds }}</td>
                            <td class="px-4 py-2">{{ $p->baths }}</td>
                            <td class="px-4 py-2">{{ $p->rooms_count }}</td>
                            <td class="px-4 py-2">{{ $p->latitude }}, {{ $p->longitude }}</td>
                            <td class="px-4 py-2">{{ $p->geo_radius_m }}</td>
                            <td class="px-4 py-2 text-right">
                                <a class="text-indigo-600 hover:underline"
                                    href="{{ route('properties.edit', $p) }}">Edit</a>
                                <span class="mx-2">·</span>
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
        </div>

        {{ $properties->links() }}
    </div>
</x-app-layout>
