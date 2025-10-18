<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Property — {{ $property->name }}</h2>
    </x-slot>
    <form method="post" action="{{ route('properties.update', $property) }}" class="space-y-6 bg-white p-6 rounded border">
        @csrf @method('put')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><x-input-label value="Name" /><x-text-input name="name" class="w-full"
                    value="{{ old('name', $property->name) }}" required /></div>
            <div><x-input-label value="Beds" /><x-text-input type="number" name="beds" class="w-full"
                    value="{{ old('beds', $property->beds) }}" /></div>
            <div><x-input-label value="Baths" /><x-text-input type="number" name="baths" class="w-full"
                    value="{{ old('baths', $property->baths) }}" /></div>
            <div><x-input-label value="Latitude" /><x-text-input name="latitude" class="w-full"
                    value="{{ old('latitude', $property->latitude) }}" /></div>
            <div><x-input-label value="Longitude" /><x-text-input name="longitude" class="w-full"
                    value="{{ old('longitude', $property->longitude) }}" /></div>
            <div><x-input-label value="Geo Radius (m)" /><x-text-input type="number" name="geo_radius_m" class="w-full"
                    value="{{ old('geo_radius_m', $property->geo_radius_m) }}" /></div>
        </div>
        <div class="flex items-center gap-2">
            <x-primary-button>Update</x-primary-button>
            <a href="{{ route('properties.index') }}" class="px-3 py-2 rounded border">Back</a>
            <form method="post" action="{{ route('properties.destroy', $property) }}"
                onsubmit="return confirm('Delete property?')" class="ml-auto">
                @csrf @method('delete')
                <button class="px-3 py-2 rounded border text-red-600">Delete</button>
            </form>
        </div>
    </form>
</x-app-layout>
