<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Room — {{ $room->name }}</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('rooms.update', [$property, $room]) }}" class="space-y-6">
            @csrf @method('put')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><x-form.label value="Name" /><x-form.input name="name" class="w-full"
                        value="{{ old('name', $room->name) }}" required /></div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $room->is_default))
                        class="rounded border-gray-300">
                    <label>Default template</label>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button>Update</x-button>
                <x-button variant="secondary" href="{{ route('rooms.index', $property) }}"
                    class="px-3 py-2 rounded border">Back</x-button>
                <form method="post" action="{{ route('rooms.destroy', [$property, $room]) }}"
                    onsubmit="return confirm('Delete room?')" class="ml-auto">
                    @csrf @method('delete')
                    <x-button variant="danger">Delete</x-button>
                </form>
            </div>
        </form>
    </x-card>
</x-app-layout>
