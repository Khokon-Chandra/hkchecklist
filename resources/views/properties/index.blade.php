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
                            <td class="px-4 py-2 text-right whitespace-nowrap">

                                <x-modal name="confirm-delete-property-{{ $p->id }}" :show="false"
                                    maxWidth="md">
                                    <div class="p-6 text-left">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete
                                            Property
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                            < class="mt-2 text-sm text-gray-600 dark:text-gray-300 text-wrap">
                                                You are about to permanently delete the property
                                                <strong>{{ $p->name }}</strong>.
                                                This will remove the property and its
                                                {{ $p->rooms_count ?? 'associated' }}
                                                rooms and related data and cannot be undone.
                                                Please confirm you want to proceed.
                                        </p>

                                        <div class="mt-6 flex items-center justify-end gap-2">
                                            <x-button variant="secondary"
                                                x-on:click="$dispatch('close')">Cancel</x-button>
                                            <form method="post" action="{{ route('properties.destroy', $p) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-button
                                                    class="bg-rose-600 hover:bg-rose-700 focus:ring-rose-500">Delete</x-button>
                                            </form>
                                        </div>
                                    </div>
                                </x-modal>
                                @role('admin|owner')
                                    <a class="text-indigo-600 hover:underline"
                                        href="{{ route('properties.edit', $p) }}">Edit</a>
                                    <span class="mx-2">·</span>
                                    <button type="button" class="text-red-600 hover:underline"
                                        @click="$dispatch('open-modal', 'confirm-delete-property-{{ $p->id }}')">
                                        Delete
                                    </button>
                                    <span class="mx-2">·</span>
                                @endrole
                                <a class="text-blue-600 hover:underline"
                                    href="{{ route('properties.rooms.index', ['property' => $p->id]) }}">Rooms</a>
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
