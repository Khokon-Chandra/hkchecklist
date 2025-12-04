<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Properties</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <form method="get" class="flex gap-2">
                <x-form.input name="q" type="text" :value="old('q', request('q'))" autofocus autocomplete="name"
                    placeholder="Search by name" />

                <x-form.select name="owner_id" :selected="request('owner_id')" class="w-40 !py-1">
                    <option value="">All owners</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected(request('owner_id') == $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </x-form.select>

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
    <script>
        function assignRoomsPanel(propertyId, allRooms, initialAttachedIds = [], propertyRoomSoreUrl,
            propertyRoomsAttachUrl) {
            return {
                propertyId,
                rooms: allRooms,
                search: '',
                selectedIds: Array.from(new Set(initialAttachedIds)), // preselect attached rooms
                newRoomName: '',
                isSaving: false,
                isCreating: false,

                get filtered() {
                    if (!this.search) return this.rooms
                    const q = this.search.toLowerCase()
                    return this.rooms.filter(r => r.name.toLowerCase().includes(q))
                },

                isSelected(id) {
                    return this.selectedIds.includes(id)
                },

                toggle(id) {
                    if (this.isSelected(id)) {
                        this.selectedIds = this.selectedIds.filter(x => x !== id)
                    } else {
                        this.selectedIds.push(id)
                    }
                },

                selectAll() {
                    this.selectedIds = this.filtered.map(r => r.id)
                },

                clearSelection() {
                    this.selectedIds = []
                },

                async createRoom() {
                    if (!this.newRoomName.trim()) return

                    this.isCreating = true
                    try {
                        api.post(propertyRoomSoreUrl, {
                            name: this.newRoomName.trim()
                        })
                        const newId = Date.now()
                        this.rooms.push({
                            id: newId,
                            name: this.newRoomName.trim(),
                            is_default: false,
                        })
                        this.selectedIds.push(newId)
                        this.newRoomName = ''
                    } finally {
                        this.isCreating = false
                    }
                },

                async save() {
                    this.isSaving = true
                    try {
                        await api.post(propertyRoomsAttachUrl, {
                            room_ids: this.selectedIds
                        })
                        this.$dispatch('close-preview-panel', `assign-rooms-${this.propertyId}`)
                        window.location.reload()
                    } catch (e) {
                        console.error(e)
                        alert('Save failed – see console for details.')
                    } finally {
                        this.isSaving = false
                    }
                },
            }
        }
    </script>

</x-app-layout>
