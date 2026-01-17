<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg sm:text-xl font-semibold leading-tight">Properties</h2>
    </x-slot>

    <div class="space-y-4 w-full max-w-full">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 px-1 sm:px-0">
            <form method="get" class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-2 flex-1 min-w-0 w-full">
                <x-form.input name="q" type="text" :value="old('q', request('q'))" autofocus autocomplete="name"
                    placeholder="Search by name" class="w-full sm:flex-1 min-w-0 max-w-full" />

                <x-form.select name="owner_id" :selected="request('owner_id')"
                    class="w-full sm:w-auto sm:min-w-[140px] sm:max-w-[200px] !py-1">
                    <option value="">All owners</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected(request('owner_id') == $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </x-form.select>

                <x-button variant="secondary" class="w-full sm:w-auto whitespace-nowrap">Filter</x-button>
                <x-button variant="secondary" :href="route('properties.index')"
                    class="w-full sm:w-auto whitespace-nowrap">Clear</x-button>
            </form>

            @role('admin|owner')
                <x-button href="{{ route('properties.create') }}"
                    class="inline-flex items-center justify-center px-3 py-2 rounded bg-indigo-600 text-white w-full sm:w-auto whitespace-nowrap">+
                    New</x-button>
            @endrole
        </div>

        <x-card class="!px-0 overflow-x-auto w-full">
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
                    @forelse($properties as $property)
                        <tr>
                            <td class="px-4 py-2">
                                @php
                                    $photoUrl = method_exists($property, 'getPhotoUrlAttribute')
                                        ? $property->photo_url
                                        : ($property->photo_path
                                            ? (Str::startsWith($property->photo_path, ['http://', 'https://'])
                                                ? $property->photo_path
                                                : asset('storage/' . $property->photo_path))
                                            : asset('images/placeholders/property.png'));
                                @endphp
                                <img src="{{ $photoUrl }}" class="h-12 w-12 rounded-xl object-cover" alt="Photo">
                            </td>
                            <td class="px-4 py-2 font-medium">{{ $property->name }}</td>
                            <td class="py-2 font-medium">{{ $property->owner->name }}</td>
                            <td class="px-4 py-2">{{ $property->rooms_count }}</td>
                            <td class="px-4 py-2">{{ $property->address ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @if ($property->latitude && $property->longitude)
                                    {{ number_format($property->latitude, 5) }},
                                    {{ number_format($property->longitude, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center whitespace-nowrap">
                                @includeIf('properties.__property_action', [
                                    'property' => $property,
                                    'rooms' => $rooms,
                                ])
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
