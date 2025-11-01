<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl flex items-center gap-2">
            Edit Task — {{ $task->name }}
        </h2>
    </x-slot>

    {{-- UPDATE FORM --}}
    <x-card>
        <form method="post" action="{{ route('tasks.update', $task) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Name --}}
                <div class="md:col-span-2">
                    <x-form.label for="name" value="Name" />
                    <x-form.input id="name" name="name" class="w-full" required :value="old('name', $task->name)" />
                    <x-form.error :messages="$errors->get('name')" />
                </div>

                {{-- Type --}}
                <div>
                    <x-form.label for="type" value="Type" />
                    <select id="type" name="type"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="room" @selected(old('type', $task->type) === 'room')>Room</option>
                        <option value="inventory" @selected(old('type', $task->type) === 'inventory')>Inventory</option>
                    </select>
                    <x-form.error :messages="$errors->get('type')" />
                </div>

                {{-- Default template --}}
                <div class="flex items-center gap-2 pt-6">
                    <input id="is_default" type="checkbox" name="is_default" value="1" @checked(old('is_default', $task->is_default))
                        class="rounded border-gray-300 dark:border-gray-700">
                    <label for="is_default">Mark as default template</label>
                </div>

                {{-- Meta (optional) --}}
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-x-4">
                        <span>Created: {{ $task->created_at?->format('Y-m-d H:i') }}</span>
                        <span>Updated: {{ $task->updated_at?->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit">Update</x-button>
                <x-button variant="secondary" href="{{ route('tasks.index') }}">Cancel</x-button>
            </div>
        </form>
    </x-card>

    {{-- DELETE FORM (separate; no nesting) --}}
    <div class="mt-4">
        <form method="post" action="{{ route('tasks.destroy', $task) }}">
            @csrf
            @method('DELETE')
            <x-button class="bg-rose-600 hover:bg-rose-700 focus:ring-rose-500"
                onclick="return confirm('Delete this task? This cannot be undone.')">
                Delete Task
            </x-button>
        </form>
    </div>
</x-app-layout>
