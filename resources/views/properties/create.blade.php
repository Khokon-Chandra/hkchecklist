<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">New Property</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('properties.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-form.label value="Name" />
                    <x-form.input name="name" class="w-full" required />
                    <x-form.error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-form.label value="Beds" />
                    <x-form.input type="number" min="0" name="beds" class="w-full" />
                </div>
                <div>
                    <x-form.label value="Baths" />
                    <x-form.input type="number" min="0" name="baths" class="w-full" />
                </div>
                <div>
                    <x-form.label value="Latitude" />
                    <x-form.input name="latitude" class="w-full" />
                </div>
                <div>
                    <x-form.label value="Longitude" />
                    <x-form.input name="longitude" class="w-full" />
                </div>
                <div>
                    <x-form.label value="Geo Radius (meters)" />
                    <x-form.input type="number" min="50" step="10" name="geo_radius_m" value="150"
                        class="w-full" />
                </div>
            </div>
            <div class="flex gap-2 mt-8">
                <x-button>Save</x-button>
                <x-button variant="secondary" href="{{ route('properties.index') }}">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
