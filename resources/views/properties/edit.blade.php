{{-- resources/views/properties/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Property
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Update this property. Latitude &amp; longitude will be updated automatically from the address if
                    left empty.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button variant="secondary" href="{{ route('properties.index') }}">
                    Back to List
                </x-button>
                <x-button variant="secondary" href="{{ route('properties.rooms.index', $property) }}">
                    Rooms
                </x-button>
                <x-button variant="secondary" href="{{ route('properties.property-tasks.index', $property) }}">
                    Property Tasks
                </x-button>
            </div>
        </div>
    </x-slot>

    <x-card>
        <form x-data="propertyEditForm()" method="post" action="{{ route('properties.update', $property) }}"
            enctype="multipart/form-data" @submit.prevent="handleSubmit($event)">
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
                    <div class="mt-1 border-2 border-dashed rounded-2xl p-4 text-center bg-gray-50/40">
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
                                class="rounded-xl object-cover h-48 w-full shadow-sm" />
                        </template>

                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview"
                                class="rounded-xl object-cover h-48 w-full shadow-sm" />
                        </template>

                        <input type="file" name="photo" class="hidden" x-ref="file" @change="preview($event)"
                            accept="image/*" />

                        <div class="mt-3 flex items-center justify-center gap-3">
                            <x-button type="button" variant="secondary" @click="$refs.file.click()">
                                Choose File
                            </x-button>

                            @if ($property->photo_path)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="remove_photo" value="1"
                                        class="rounded border-gray-300">
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
                        <x-form.input name="name" class="w-full" required :value="old('name', $property->name)"
                            placeholder="e.g. Seaside Apartment 3B" />
                        <x-form.error :messages="$errors->get('name')" />
                    </div>

                    {{-- Address --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Address (optional)" />
                        <x-form.input name="address" class="w-full" x-model="address" @blur="geocodeIfNeeded()"
                            :value="old('address', $property->address)" placeholder="e.g. 1600 Amphitheatre Pkwy, Mountain View" />

                        <div class="mt-1 space-y-1">
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                                </svg>
                                <span>
                                    If you provide an address and leave Latitude/Longitude empty,
                                    we’ll auto-fill them for you.
                                </span>
                            </p>

                            <p class="text-xs text-gray-500">
                                You do <span class="font-semibold">not</span> need to fetch latitude/longitude manually.
                                They’re updated automatically when you save.
                            </p>

                            <template x-if="isGeocoding">
                                <p class="text-xs text-indigo-600 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3a9 9 0 019 9m-9 9a9 9 0 01-9-9" />
                                    </svg>
                                    Fetching coordinates&hellip; please wait.
                                </p>
                            </template>

                            <template x-if="geocodeError">
                                <p class="text-xs text-red-600" x-text="geocodeError"></p>
                            </template>
                        </div>

                        <x-form.error :messages="$errors->get('address')" />
                    </div>

                    {{-- Latitude --}}
                    <div>
                        <x-form.label value="Latitude (optional)" />
                        <x-form.input name="latitude" class="w-full" x-model="latitude" :value="old('latitude', $property->latitude)"
                            placeholder="Auto-filled from address" />
                        <x-form.error :messages="$errors->get('latitude')" />
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <x-form.label value="Longitude (optional)" />
                        <x-form.input name="longitude" class="w-full" x-model="longitude" :value="old('longitude', $property->longitude)"
                            placeholder="Auto-filled from address" />
                        <x-form.error :messages="$errors->get('longitude')" />
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-8 justify-end">
                <x-button x-bind:disabled="isGeocoding"
                    x-bind:class="isGeocoding ? 'opacity-60 cursor-not-allowed' : ''">
                    <span x-show="!isGeocoding">Update</span>
                    <span x-show="isGeocoding">Fetching coordinates…</span>
                </x-button>

                <x-button variant="secondary" href="{{ route('properties.index') }}">
                    Cancel
                </x-button>
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

                // UX state
                isGeocoding: false,
                geocodeError: '',

                preview(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.previewUrl = URL.createObjectURL(file);
                },

                async handleSubmit(event) {
                    this.geocodeError = '';

                    if (this.isGeocoding) {
                        return;
                    }

                    const needsGeocode = this.address && (!this.latitude || !this.longitude);

                    if (needsGeocode) {
                        await this.geocodeIfNeeded(true); // force on submit
                    }

                    // If address is set but coordinates are still missing after an attempt, block submit
                    if (this.address && (!this.latitude || !this.longitude)) {
                        this.geocodeError ||=
                            'We could not fetch coordinates automatically. Please enter Latitude/Longitude manually.';
                        return;
                    }

                    event.target.submit();
                },

                async geocodeIfNeeded(force = false) {
                    if (!this.address) return;

                    // Skip if not forced and both coords already exist
                    if (!force && this.latitude && this.longitude) return;

                    this.isGeocoding = true;
                    this.geocodeError = '';

                    try {
                        const url =
                            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.address)}&limit=1`;
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`HTTP ${res.status}`);
                        }

                        const data = await res.json();

                        if (Array.isArray(data) && data.length) {
                            this.latitude = data[0].lat;
                            this.longitude = data[0].lon;
                        } else {
                            this.geocodeError =
                                'No coordinates found for this address. You can still enter them manually.';
                        }
                    } catch (error) {
                        console.warn('Geocoding failed', error);
                        this.geocodeError =
                            'Unable to fetch coordinates right now. Please try again or enter them manually.';
                    } finally {
                        this.isGeocoding = false;
                    }
                },
            }
        }
    </script>
</x-app-layout>
