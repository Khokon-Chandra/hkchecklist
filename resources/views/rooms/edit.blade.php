{{-- resources/views/rooms/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl flex items-center gap-2">
            Edit Room — {{ $room->name }}
        </h2>
    </x-slot>

    {{-- UPDATE FORM (inside card) --}}
    <x-card>
        <form method="post" action="{{ route('rooms.update', $room) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <x-form.label for="name" value="Name" />
                    <x-form.input id="name" name="name" class="w-full" required :value="old('name', $room->name)" />
                    <x-form.error :messages="$errors->get('name')" />
                </div>

                {{-- Default template toggle --}}
                <div class="md:col-span-2">
                    <label for="is_default" class="inline-flex items-center gap-2">
                        <input id="is_default" type="checkbox" name="is_default" value="1"
                            @checked(old('is_default', $room->is_default)) class="rounded border-gray-300 dark:border-gray-700">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Mark as default template
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Default rooms can be auto-assigned when new properties are created.
                    </p>
                </div>

                {{-- Meta (read-only) --}}
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-4">
                        <span>Created: {{ $room->created_at?->format('Y-m-d H:i') }}</span>
                        <span>Updated: {{ $room->updated_at?->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit">Update</x-button>
                <x-button variant="secondary" href="{{ route('rooms.index') }}">Cancel</x-button>
            </div>
        </form>
    </x-card>


</x-app-layout>
