<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">New Property</h2>
    </x-slot>

    <x-card>
        <form x-data="propertyForm()" x-init="init()" method="post" action="{{ route('properties.store') }}"
            enctype="multipart/form-data">
            @csrf

            {{-- If the current user is an owner, set owner_id automatically --}}
            @if (auth()->user()->hasRole('owner'))
                <input type="hidden" name="owner_id" value="{{ auth()->id() }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Image uploader --}}
                <div class="lg:col-span-1">
                    <x-form.label value="Property Photo" />
                    <div class="mt-1 border-2 border-dashed rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer"
                        :class="dragOver ? 'border-indigo-500' : 'border-gray-300'" @click="$refs.file.click()"
                        @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop($event)">
                        <template x-if="!previewUrl">
                            <div class="text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l5.5 5.5M21 7l-5.5 5.5M12 3v12" />
                                </svg>
                                <p class="mt-2 text-sm">Drag & drop or click to upload</p>
                            </div>
                        </template>
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="Preview" class="rounded-xl object-cover h-48 w-full" />
                        </template>

                        <input type="file" name="photo" x-ref="file" class="hidden" @change="preview($event)"
                            accept="image/*" />
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
                                    <option value="{{ $id }}" @selected(old('owner_id') == $id)>{{ $name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                            <x-form.error :messages="$errors->get('owner_id')" />
                        </div>
                    @endrole

                    {{-- Name --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Name" />
                        <x-form.input name="name" class="w-full" required :value="old('name')" />
                        <x-form.error :messages="$errors->get('name')" />
                    </div>

                    {{-- Address --}}
                    <div class="md:col-span-2">
                        <x-form.label value="Address (optional)" />
                        <x-form.input name="address" class="w-full" x-model="address" @blur="geocodeIfNeeded()"
                            :value="old('address')" placeholder="e.g. 1600 Amphitheatre Pkwy, Mountain View" />
                        <p class="text-xs text-gray-500 mt-1">
                            If you provide an address and leave coordinates empty, we’ll auto-fill Lat/Lng.
                        </p>
                        <x-form.error :messages="$errors->get('address')" />
                    </div>

                    {{-- Latitude / Longitude --}}
                    <div>
                        <x-form.label value="Latitude (optional)" />
                        <x-form.input name="latitude" class="w-full" x-model="latitude" :value="old('latitude')" />
                        <x-form.error :messages="$errors->get('latitude')" />
                    </div>

                    <div>
                        <x-form.label value="Longitude (optional)" />
                        <x-form.input name="longitude" class="w-full" x-model="longitude" :value="old('longitude')" />
                        <x-form.error :messages="$errors->get('longitude')" />
                    </div>


                </div>
            </div>

            <div class="flex gap-2 mt-8">
                <x-button>Save</x-button>
                <x-button variant="secondary" href="{{ route('properties.index') }}">Cancel</x-button>
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
                init() {},
                preview(e) {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    this.previewUrl = URL.createObjectURL(file);
                },
                handleDrop(evt) {
                    this.dragOver = false;
                    const file = evt.dataTransfer.files?.[0];
                    if (!file) return;
                    this.$refs.file.files = evt.dataTransfer.files;
                    this.preview({
                        target: {
                            files: this.$refs.file.files
                        }
                    });
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
