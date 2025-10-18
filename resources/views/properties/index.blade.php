<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Properties</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <form method="get" class="flex gap-2">
                <input name="q" value="{{ request('q') }}" class="w-64 rounded border-gray-300"
                    placeholder="Search name...">
                <x-button variant="">Filter</x-button>
            </form>
            @can('create', App\Domain\Properties\Models\Property::class)
                <a href="{{ route('properties.create') }}"
                    class="inline-flex items-center px-3 py-2 rounded bg-indigo-600 text-white">+ New</a>
            @endcan
        </div>

        <div class="overflow-hidden rounded border bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2">Beds</th>
                        <th class="px-4 py-2">Baths</th>
                        <th class="px-4 py-2">Lat/Lng</th>
                        <th class="px-4 py-2">Radius (m)</th>
                        <th class="px-4 py-2 w-40"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($properties as $p)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                            <td class="px-4 py-2 text-center">{{ $p->beds }}</td>
                            <td class="px-4 py-2 text-center">{{ $p->baths }}</td>
                            <td class="px-4 py-2 text-center">{{ $p->latitude }}, {{ $p->longitude }}</td>
                            <td class="px-4 py-2 text-center">{{ $p->geo_radius_m }}</td>
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
