<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Room — {{ $room->name }}</h2>
    </x-slot>

    <x-card>
        {{-- UPDATE FORM --}}
        <form id="room-update-form" method="post" action="{{ route('rooms.update', [$property, $room]) }}"
            class="space-y-6">
            @csrf
            @method('put')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-form.label value="Name" />
                    <x-form.input name="name" class="w-full" value="{{ old('name', $room->name) }}" required />
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <input id="is_default" type="checkbox" name="is_default" value="1" @checked(old('is_default', $room->is_default))
                        class="rounded border-gray-300">
                    <label for="is_default">Default template</label>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" form="room-update-form">Update</x-button>

                <x-button variant="secondary" href="{{ route('rooms.index', $property) }}"
                    class="px-3 py-2 rounded border">
                    Back
                </x-button>
                <x-button type="submit" variant="danger" form="delete_room">Delete</x-button>
            </div>
        </form>

        {{-- DELETE FORM (separate, not nested) --}}
        <form id="delete_room" method="post" action="{{ route('rooms.destroy', [$property, $room]) }}"
            onsubmit="return confirm('Delete room?')" class="mt-4">
            @csrf
            @method('delete')

        </form>
    </x-card>
</x-app-layout>
