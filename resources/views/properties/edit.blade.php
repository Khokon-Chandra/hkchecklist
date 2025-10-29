<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Property</h2>
    </x-slot>

    <x-card>
        <form x-data="propertyEditForm()" method="post" action="{{ route('properties.update', $property) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- If the current user is an owner (not admin), lock owner_id --}}
            @if (!($isAdmin ?? false) && auth()->user()->hasRole('owner'))
                <input type="hidden" name="owner_id" value="{{ auth()->id() }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Image (current + replace/remove) --}}
                <div class="lg:col-span-1">
                    <x-form.label value="Property Photo" />
                    <div class="mt-1 border-2 border-dashed rounded-2xl p-4 text-center">
                        @php
                            $photoUrl = method_exists($property, 'getPhotoUrlAttribute')
                                ? $property->photo_url
                                : ($property->photo_path
                                    ? (Str::startsWith($property->photo_path, ['http://', 'https://'])
                                        ? $property->photo_path
                                        : asset('storage/' . $property->photo_path))
                                    : asset('images/placeholders/property.png'));
                        @endphp

                        <template x-if="!previewUrl">
                            <img src="{{ $photoUrl }}" alt="Current photo"
                                class="rounded-xl object-cover h-48 w-full" />
                        </template>
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview" class="rounded-xl object-cover h-48 w-full" />
                        </template>

                        <input type="file" name="photo" class="hidden" x-ref="file" @change="preview($event)"
                            accept="image/*" />
                        <div class="mt-3 flex items-center justify-center gap-3">
                            <x-button type="button" variant="secondary" @click="$refs.file.click()">Choose
                                File</x-button>
                            @if ($property->photo_path)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="remove_photo" value="1" class="rounded">
                                    Remove photo
                                </label>
                            @endif
                        </div>
                        @error('photo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Right column: Fields --}}
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Admin-only owner select --}}
                    @role('admin')
                        <div class="md:col-span-2">
                            <x-form.label value="Owner" />
                            <x-form.select name="owner_id" class="w-full">
                                @foreach ($owners ?? [] as $id => $name)
                                    <option value="{{ $id }}" @selected(old('owner_id', $property->owner_id) == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                            <x-form.error :messages="$errors->get('owner_id')" />
                        </div>
                    @endrole

                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Name" />
                        <x-form.input name="name" class="w-full" required :value="old('name', $property->name)" />
                        <x-form.error :messages="$errors->get('name')" />
                    </div>

                    {{-- Address --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Address (optional)" />
                        <x-form.input name="address" class="w-full" x-model="address" @blur="geocodeIfNeeded()"
                            :value="old('address', $property->address)" placeholder="e.g. 1600 Amphitheatre Pkwy, Mountain View" />
                        <p class="text-xs text-gray-500 mt-1">
                            If you provide an address and leave coordinates empty, we’ll auto-fill Lat/Lng.
                        </p>
                        <x-form.error :messages="$errors->get('address')" />
                    </div>

                    {{-- Latitude / Longitude --}}
                    <div>
                        <x-form.label value="Latitude (optional)" />
                        <x-form.input name="latitude" class="w-full" x-model="latitude" :value="old('latitude', $property->latitude)" />
                        <x-form.error :messages="$errors->get('latitude')" />
                    </div>

                    <div>
                        <x-form.label value="Longitude (optional)" />
                        <x-form.input name="longitude" class="w-full" x-model="longitude" :value="old('longitude', $property->longitude)" />
                        <x-form.error :messages="$errors->get('longitude')" />
                    </div>

                    {{-- Geo Radius --}}
                    <div>
                        <x-form.label value="Geo Radius (meters)" />
                        <x-form.input type="number" min="50" step="10" name="geo_radius_m" :value="old('geo_radius_m', $property->geo_radius_m ?? 150)"
                            class="w-full" />
                        <x-form.error :messages="$errors->get('geo_radius_m')" />
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-8">
                <x-button>Update</x-button>
                <x-button variant="secondary" href="{{ route('properties.index') }}">Cancel</x-button>
            </div>
        </form>
    </x-card>

    {{-- Alpine helpers --}}
    <script>
        function propertyEditForm() {
            return {
                address: @json(old('address', $property->address)),
                latitude: @json(old('latitude', $property->latitude)),
                longitude: @json(old('longitude', $property->longitude)),
                previewUrl: null,
                preview(e) {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    this.previewUrl = URL.createObjectURL(file);
                },
                async geocodeIfNeeded() {
                    if (!this.address || this.latitude || this.longitude) return;
                    try {
                        const url =
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.address)}&limit=1`;
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (Array.isArray(data) && data.length) {
                            this.latitude = data[0].lat;
                            this.longitude = data[0].lon;
                        }
                    } catch (e) {
                        console.warn('Geocoding failed', e);
                    }
                }
            }
        }
    </script>
</x-app-layout>
