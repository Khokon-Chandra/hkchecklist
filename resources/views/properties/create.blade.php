<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">New Property</h2>
    </x-slot>
    <form method="post" action="{{ route('properties.store') }}" class="space-y-6 bg-white p-6 rounded border">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Name" />
                <x-text-input name="name" class="w-full" required />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label value="Beds" />
                <x-text-input type="number" min="0" name="beds" class="w-full" />
            </div>
            <div>
                <x-input-label value="Baths" />
                <x-text-input type="number" min="0" name="baths" class="w-full" />
            </div>
            <div>
                <x-input-label value="Latitude" />
                <x-text-input name="latitude" class="w-full" />
            </div>
            <div>
                <x-input-label value="Longitude" />
                <x-text-input name="longitude" class="w-full" />
            </div>
            <div>
                <x-input-label value="Geo Radius (meters)" />
                <x-text-input type="number" min="50" step="10" name="geo_radius_m" value="150"
                    class="w-full" />
            </div>
        </div>
        <div class="flex gap-2">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('properties.index') }}" class="px-3 py-2 rounded border">Cancel</a>
        </div>
    </form>
</x-app-layout>
