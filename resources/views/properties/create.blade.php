{{-- resources/views/properties/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    New Property
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Add a new property. Latitude &amp; longitude will be updated automatically from the address.
                </p>
            </div>

            <x-button variant="secondary" href="{{ route('properties.index') }}">
                Back to List
            </x-button>
        </div>
    </x-slot>

    <x-card>
        <form
            x-data="propertyForm()"
            x-init="init()"
            method="post"
            action="{{ route('properties.store') }}"
            enctype="multipart/form-data"
            @submit.prevent="handleSubmit($event)"
        >
            @csrf

            {{-- If the current user is an owner, set owner_id automatically --}}
            @if (auth()->user()->hasRole('owner'))
                <input type="hidden" name="owner_id" value="{{ auth()->id() }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Image uploader --}}
                <div class="lg:col-span-1">
                    <x-form.label value="Property Photo" />

                    <div
                        class="mt-1 border-2 border-dashed rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-colors"
                        :class="dragOver ? 'border-indigo-500 bg-indigo-50/40' : 'border-gray-300 bg-gray-50/40 dark:bg-gray-800'"
                        @click="$refs.file.click()"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <template x-if="!previewUrl">
                            <div class="text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l5.5 5.5M21 7l-5.5 5.5M12 3v12" />
                                </svg>
                                <p class="mt-2 text-sm font-medium">Drag &amp; drop or click to upload</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    JPG, PNG, WebP — up to ~5MB
                                </p>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="w-full">
                                <img
                                    :src="previewUrl"
                                    alt="Preview"
                                    class="rounded-xl object-cover h-48 w-full shadow-sm"
                                />
                                <p class="mt-2 text-xs text-gray-500">
                                    Click or drop a new file to replace the photo.
                                </p>
                            </div>
                        </template>

                        <input
                            type="file"
                            name="photo"
                            x-ref="file"
                            class="hidden"
                            @change="preview($event)"
                            accept="image/*"
                        />
                    </div>

                    @error('photo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Right column: Form fields --}}
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Admin can assign owner --}}
                    @role('admin')
                        <div class="md:col-span-2">
                            <x-form.label value="Owner" />
                            {{-- Expecting $owners = [id => name] from controller --}}
                            <x-form.select name="owner_id" class="w-full">
                                <option value="" disabled selected>— Select Owner —</option>
                                @foreach ($owners ?? [] as $id => $name)
                                    <option value="{{ $id }}" @selected(old('owner_id') == $id)>{{ $name }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.error :messages="$errors->get('owner_id')" />
                        </div>
                    @endrole

                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Name" />
                        <x-form.input
                            name="name"
                            class="w-full"
                            required
                            :value="old('name')"
                            placeholder="e.g. Seaside Apartment 3B"
                        />
                        <x-form.error :messages="$errors->get('name')" />
                    </div>

                    {{-- Address --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Address (optional)" />
                        <x-form.input
                            name="address"
                            class="w-full"
                            x-model="address"
                            @blur="geocodeIfNeeded()"
                            :value="old('address')"
                            placeholder="e.g. 1600 Amphitheatre Pkwy, Mountain View"
                        />

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
                        <x-form.input
                            name="latitude"
                            class="w-full"
                            x-model="latitude"
                            :value="old('latitude')"
                            placeholder="Auto-filled from address"
                        />
                        <x-form.error :messages="$errors->get('latitude')" />
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <x-form.label value="Longitude (optional)" />
                        <x-form.input
                            name="longitude"
                            class="w-full"
                            x-model="longitude"
                            :value="old('longitude')"
                            placeholder="Auto-filled from address"
                        />
                        <x-form.error :messages="$errors->get('longitude')" />
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap justify-end gap-2">
                {{-- Plain save --}}
                <x-button
                    type="submit"
                    name="attach"
                    value="none"
                    x-bind:disabled="isGeocoding"
                    x-bind:class="isGeocoding ? 'opacity-60 cursor-not-allowed' : ''"
                >
                    <span x-show="!isGeocoding">Save</span>
                    <span x-show="isGeocoding">Fetching coordinates…</span>
                </x-button>

                {{-- Save + default rooms --}}
                <x-button
                    type="submit"
                    name="attach"
                    value="rooms"
                    class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500"
                    x-bind:disabled="isGeocoding"
                    x-bind:class="isGeocoding ? 'opacity-60 cursor-not-allowed' : ''"
                >
                    Save + Assign Default Rooms
                </x-button>

                {{-- Save + default rooms & tasks --}}
                <x-button
                    type="submit"
                    name="attach"
                    value="rooms_tasks"
                    class="bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500"
                    x-bind:disabled="isGeocoding"
                    x-bind:class="isGeocoding ? 'opacity-60 cursor-not-allowed' : ''"
                >
                    Save + Assign Default Rooms &amp; Tasks
                </x-button>

                <x-button variant="secondary" href="{{ route('properties.index') }}">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>

    {{-- Alpine helpers --}}
    <script>
        function propertyForm() {
            return {
                address: @json(old('address', '')),
                latitude: @json(old('latitude', '')),
                longitude: @json(old('longitude', '')),
                previewUrl: null,
                dragOver: false,

                // UX state
                isGeocoding: false,
                geocodeError: '',

                init() {
                    // If old() has coords, we can pre-fill without hitting API
                },

                preview(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.previewUrl = URL.createObjectURL(file);
                },

                handleDrop(evt) {
                    this.dragOver = false;
                    const file = evt.dataTransfer.files?.[0];
                    if (!file) return;

                    this.$refs.file.files = evt.dataTransfer.files;
                    this.preview({ target: { files: this.$refs.file.files } });
                },

                async handleSubmit(event) {
                    this.geocodeError = '';

                    if (this.isGeocoding) {
                        return;
                    }

                    const needsGeocode = this.address && (!this.latitude || !this.longitude);

                    if (needsGeocode) {
                        await this.geocodeIfNeeded(true); // force geocoding on submit
                    }

                    // If address is set but coordinates are still missing after attempted geocode,
                    // block submit so the owner doesn’t think it silently worked.
                    if (this.address && (!this.latitude || !this.longitude)) {
                        this.geocodeError ||= 'We could not fetch coordinates automatically. Please enter Latitude/Longitude manually.';
                        return;
                    }

                    event.target.submit();
                },

                async geocodeIfNeeded(force = false) {
                    if (!this.address) return;

                    if (!force && this.latitude && this.longitude) return;

                    this.isGeocoding = true;
                    this.geocodeError = '';

                    try {
                        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.address)}&limit=1`;
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
                            this.geocodeError = 'No coordinates found for this address. You can still enter them manually.';
                        }
                    } catch (error) {
                        console.warn('Geocoding failed', error);
                        this.geocodeError = 'Unable to fetch coordinates right now. Please try again or enter them manually.';
                    } finally {
                        this.isGeocoding = false;
                    }
                },
            }
        }
    </script>
</x-app-layout>
