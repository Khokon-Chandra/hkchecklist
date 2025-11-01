<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl flex items-center gap-2">
            New Task
        </h2>
    </x-slot>

    <x-card>
        <form method="post" action="{{ route('tasks.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <x-form.label for="name" value="Name" />
                    <x-form.input id="name" name="name" class="w-full" required
                                  :value="old('name')" placeholder="e.g. Mop floor, Restock soap" />
                    <x-form.error :messages="$errors->get('name')" />
                </div>

                {{-- Type --}}
                <div>
                    <x-form.label for="type" value="Type" />
                    <select id="type" name="type"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="room" @selected(old('type')==='room')>Room</option>
                        <option value="inventory" @selected(old('type')==='inventory')>Inventory</option>
                    </select>
                    <x-form.error :messages="$errors->get('type')" />
                </div>

                {{-- Default template --}}
                <div class="flex items-center gap-2 pt-6">
                    <input id="is_default" type="checkbox" name="is_default" value="1"
                           @checked(old('is_default'))
                           class="rounded border-gray-300 dark:border-gray-700">
                    <label for="is_default">Mark as default template</label>
                </div>
            </div>

            <div class="flex gap-2">
                <x-button type="submit">Save</x-button>
                <x-button variant="secondary" href="{{ route('tasks.index') }}">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
