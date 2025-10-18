<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Property — {{ $property->name }}</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('properties.update', $property) }}" class="space-y-6">
            @csrf @method('put')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><x-form.label value="Name" /><x-form.input name="name" class="w-full"
                        value="{{ old('name', $property->name) }}" required /></div>
                <div><x-form.label value="Beds" /><x-form.input type="number" name="beds" class="w-full"
                        value="{{ old('beds', $property->beds) }}" /></div>
                <div><x-form.label value="Baths" /><x-form.input type="number" name="baths" class="w-full"
                        value="{{ old('baths', $property->baths) }}" /></div>
                <div><x-form.label value="Latitude" /><x-form.input name="latitude" class="w-full"
                        value="{{ old('latitude', $property->latitude) }}" /></div>
                <div><x-form.label value="Longitude" /><x-form.input name="longitude" class="w-full"
                        value="{{ old('longitude', $property->longitude) }}" /></div>
                <div><x-form.label value="Geo Radius (m)" /><x-form.input type="number" name="geo_radius_m"
                        class="w-full" value="{{ old('geo_radius_m', $property->geo_radius_m) }}" /></div>
            </div>
            <div class="flex items-center gap-2">
                <x-button>Update</x-button>
                <x-button variant="secondary" href="{{ route('properties.index') }}"
                    class="px-3 py-2 rounded border">Back</x-button>
                <form method="post" action="{{ route('properties.destroy', $property) }}"
                    onsubmit="return confirm('Delete property?')" class="ml-auto">
                    @csrf @method('delete')
                    <button class="px-3 py-2 rounded border form.red-600">Delete</button>
                </form>
            </div>
        </form>
    </x-card>
</x-app-layout>
