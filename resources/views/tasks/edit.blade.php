<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Edit Task — {{ $task->name }}</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('tasks.update', [$property, $room, $task]) }}" class="space-y-6 ">
            @csrf @method('put')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <x-form.label value="Task" /><x-form.input name="name" class="w-full"
                        value="{{ old('name', $task->name) }}" required />
                </div>
                <div>
                    <x-form.label value="Type" />
                    <x-form.select name="type" class="w-full rounded border-gray-300">
                        <option value="room" @selected($task->type === 'room')>Room</option>
                        <option value="inventory" @selected($task->type === 'inventory')>Inventory</option>
                    </x-form.select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $task->is_default))
                        class="rounded border-gray-300">
                    <label>Default template</label>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-button>Update</x-button>
                <x-button variant="secondary" href="{{ route('tasks.index', [$property, $room]) }}"
                    class="px-3 py-2 rounded border">Back</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
