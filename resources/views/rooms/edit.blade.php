<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Room — {{ $room->name }}</h2>
    </x-slot>
    <form method="post" action="{{ route('rooms.update', [$property, $room]) }}"
        class="space-y-6 bg-white p-6 rounded border">
        @csrf @method('put')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><x-input-label value="Name" /><x-text-input name="name" class="w-full"
                    value="{{ old('name', $room->name) }}" required /></div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $room->is_default))
                    class="rounded border-gray-300">
                <label>Default template</label>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-primary-button>Update</x-primary-button>
            <a href="{{ route('rooms.index', $property) }}" class="px-3 py-2 rounded border">Back</a>
            <form method="post" action="{{ route('rooms.destroy', [$property, $room]) }}"
                onsubmit="return confirm('Delete room?')" class="ml-auto">
                @csrf @method('delete')
                <button class="px-3 py-2 rounded border text-red-600">Delete</button>
            </form>
        </div>
    </form>
</x-app-layout>
