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

        <x-card class="!px-0 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="dark:bg-dark-eval-1">
                    <tr class="uppercase text-left">
                        <th class="px-4">Photo</th>
                        <th class="px-4">Name</th>
                        <th class="px-4">Owner</th>
                        <th class="px-4">Rooms Count</th>
                        <th class="px-4">Address</th>
                        <th class="px-4">Lat/Lng</th>
                        <th class="text-center px-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($properties as $p)
                        <tr>
                            <td class="px-4 py-2">
                                @php
                                    $photoUrl = method_exists($p, 'getPhotoUrlAttribute')
                                        ? $p->photo_url
                                        : ($p->photo_path
                                            ? (Str::startsWith($p->photo_path, ['http://', 'https://'])
                                                ? $p->photo_path
                                                : asset('storage/' . $p->photo_path))
                                            : asset('images/placeholders/property.png'));
                                @endphp
                                <img src="{{ $photoUrl }}" class="h-12 w-12 rounded-xl object-cover" alt="Photo">
                            </td>
                            <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                            <td class="py-2 font-medium">{{ $p->owner->name }}</td>
                            <td class="px-4 py-2">{{ $p->rooms_count }}</td>
                            <td class="px-4 py-2">{{ $p->address ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @if ($p->latitude && $p->longitude)
                                    {{ number_format($p->latitude, 5) }}, {{ number_format($p->longitude, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center whitespace-nowrap">
                                @includeIf('properties.__property_action', ['p' => $p])
                            </td>


                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-500" colspan="10">No properties yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>


        {{ $properties->links() }}
    </div>
</x-app-layout>
