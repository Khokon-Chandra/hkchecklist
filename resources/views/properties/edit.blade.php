{{-- resources/views/properties/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 max-w-full overflow-hidden">
            <div class="flex-1 min-w-0 max-w-full">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
                    Edit Property
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 break-words">
                    Update this property. Latitude &amp; longitude will be updated automatically from the address if
                    left empty.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-shrink-0 w-full sm:w-auto">
                <x-button variant="secondary" href="{{ route('properties.index') }}" class="w-full sm:w-auto text-center whitespace-nowrap">
                    Back to List
                </x-button>
                <x-button variant="secondary" href="{{ route('properties.rooms.index', $property) }}" class="w-full sm:w-auto text-center whitespace-nowrap">
                    Rooms
                </x-button>
                <x-button variant="secondary" href="{{ route('properties.property-tasks.index', $property) }}" class="w-full sm:w-auto text-center whitespace-nowrap">
                    <span class="hidden sm:inline">Property Tasks</span>
                    <span class="sm:hidden">Tasks</span>
                </x-button>
            </div>
        </div>
    </x-slot>

    <x-card class="max-w-full overflow-hidden">
        <form x-data="propertyEditForm()" x-init="init()" method="post" action="{{ route('properties.update', $property) }}"
            enctype="multipart/form-data" @submit.prevent="handleSubmit($event)" class="max-w-full overflow-hidden">
            @csrf
            @method('PUT')

            {{-- If the current user is an owner (not admin), lock owner_id --}}
            @if (!($isAdmin ?? false) && auth()->user()->hasRole('owner'))
                <input type="hidden" name="owner_id" value="{{ auth()->id() }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 max-w-full">
                {{-- Left column: Image (current + replace/remove) --}}
                <div class="lg:col-span-1 max-w-full overflow-hidden">
                    <x-form.label value="Property Photo" />
                    <div class="mt-1 border-2 border-dashed rounded-xl sm:rounded-2xl p-3 sm:p-4 text-center bg-gray-50/40 max-w-full overflow-hidden">
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
                                class="rounded-xl object-cover h-48 w-full shadow-sm max-w-full" />
                        </template>

                        <template x-if="previewUrl">
                            <div class="w-full max-w-full overflow-hidden">
                                <img :src="previewUrl" alt="Preview"
                                    class="rounded-xl object-cover h-48 w-full shadow-sm max-w-full" />
                                <template x-if="previewUrl && !hasFileSelected">
                                    <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800 max-w-full overflow-hidden">
                                        <p class="font-semibold break-words">⚠️ Image preview restored</p>
                                        <p class="mt-1 break-words">Please click "Choose File" to re-select the image file. Browser security requires this after a page refresh.</p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <input type="file" name="photo" class="hidden" x-ref="file" @change="preview($event)"
                            accept="image/*" />

                        <div class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3 max-w-full overflow-hidden">
                            <x-button type="button" variant="secondary" @click="$refs.file.click()" class="w-full sm:w-auto whitespace-nowrap">
                                Choose File
                            </x-button>

                            @if ($property->photo_path)
                                <label class="inline-flex items-center justify-center gap-2 text-sm text-gray-600 cursor-pointer break-words">
                                    <x-form.checkbox name="remove_photo" value="1" />
                                    <span class="break-words">Remove photo</span>
                                </label>
                            @endif
                        </div>

                        @error('photo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Right column: Fields --}}
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 max-w-full overflow-hidden">
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

            <div class="flex flex-col sm:flex-row gap-2 mt-6 sm:mt-8 sm:justify-end max-w-full">
                <x-button x-bind:disabled="isGeocoding"
                    x-bind:class="isGeocoding ? 'opacity-60 cursor-not-allowed' : ''" class="w-full sm:w-auto whitespace-nowrap">
                    <span x-show="!isGeocoding">Update</span>
                    <span x-show="isGeocoding">Fetching coordinates…</span>
                </x-button>

                <x-button variant="secondary" href="{{ route('properties.index') }}" class="w-full sm:w-auto whitespace-nowrap">
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
                hasFileSelected: false, // Track if file input has a file

                // UX state
                isGeocoding: false,
                geocodeError: '',
                isSubmitting: false, // Flag to prevent re-triggering submit handler

                init() {
                    // Restore preview from sessionStorage if it exists (e.g., after form error)
                    const savedPreview = sessionStorage.getItem('property_photo_preview_edit');
                    if (savedPreview) {
                        this.previewUrl = savedPreview;
                        this.hasFileSelected = false; // File input is cleared by browser on refresh
                    }

                    // Check if file input has a file (in case it was preserved somehow)
                    this.$nextTick(() => {
                        if (this.$refs.file && this.$refs.file.files.length > 0) {
                            this.hasFileSelected = true;
                            // If file is actually present, update preview from it
                            const file = this.$refs.file.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    this.previewUrl = e.target.result;
                                    sessionStorage.setItem('property_photo_preview_edit', e.target.result);
                                };
                                reader.readAsDataURL(file);
                            }
                        }
                    });

                    // Only clear sessionStorage if form loads without any errors AND no file is selected
                    // This means it was a successful submission (page redirected back fresh)
                    @if($errors->isEmpty())
                        // Small delay to ensure file check completes first
                        setTimeout(() => {
                            if (!this.hasFileSelected) {
                                sessionStorage.removeItem('property_photo_preview_edit');
                            }
                        }, 100);
                    @endif
                },

                preview(event) {
                    const file = event.target.files?.[0];
                    if (!file) {
                        this.hasFileSelected = false;
                        return;
                    }

                    this.hasFileSelected = true;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previewUrl = e.target.result;
                        // Save to sessionStorage to preserve on page refresh
                        sessionStorage.setItem('property_photo_preview_edit', e.target.result);
                    };
                    reader.readAsDataURL(file);
                },

                async handleSubmit(event) {
                    // If we're already submitting (after geocoding attempt), let it proceed
                    if (this.isSubmitting) {
                        return;
                    }

                    this.geocodeError = '';

                    if (this.isGeocoding) {
                        event.preventDefault();
                        return;
                    }

                    const needsGeocode = this.address && (!this.latitude || !this.longitude);

                    if (needsGeocode) {
                        event.preventDefault();
                        await this.geocodeIfNeeded(true); // force on submit

                        // If geocoding fails, show a warning but allow submission to continue
                        // since coordinates are optional
                        if (this.address && (!this.latitude || !this.longitude)) {
                            this.geocodeError =
                                'Could not find coordinates for this address. You can continue without them or enter them manually.';
                        }

                        // Set flag and submit the form directly
                        // Don't clear sessionStorage here - let it persist in case of backend errors
                        this.isSubmitting = true;
                        event.target.submit();
                        return;
                    }

                    // Don't clear sessionStorage here - it will be cleared in init() if submission was successful
                    // This way, if there's a backend validation error, the preview will be restored
                },

                async geocodeIfNeeded(force = false) {
                    if (!this.address) return;

                    // Skip if not forced and both coords already exist
                    if (!force && this.latitude && this.longitude) return;

                    this.isGeocoding = true;
                    this.geocodeError = '';

                    try {
                        const apiKey = @json(config('services.google.geocoding_api_key'));
                        if (!apiKey) {
                            throw new Error('Google Geocoding API key is not configured');
                        }

                        const url =
                            `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(this.address)}&key=${apiKey}`;
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`HTTP ${res.status}`);
                        }

                        const data = await res.json();

                        if (data.status === 'OK' && data.results && data.results.length > 0) {
                            const location = data.results[0].geometry.location;
                            this.latitude = location.lat;
                            this.longitude = location.lng;
                        } else if (data.status === 'ZERO_RESULTS') {
                            this.geocodeError =
                                'No coordinates found for this address. You can still enter them manually.';
                        } else {
                            throw new Error(`Geocoding API error: ${data.status}`);
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
