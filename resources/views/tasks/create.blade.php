<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Task — {{ $room->name }}</h2>
    </x-slot>
    <form method="post" action="{{ route('tasks.store', [$property, $room]) }}"
        class="space-y-6 bg-white p-6 rounded border">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <x-input-label value="Task" />
                <x-text-input name="name" class="w-full" required />
            </div>
            <div>
                <x-input-label value="Type" />
                <select name="type" class="w-full rounded border-gray-300">
                    <option value="room">Room</option>
                    <option value="inventory">Inventory</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300">
                <label>Default template</label>
            </div>
        </div>
        <div class="flex gap-2">
            <x-primary-button>Save</x-primary-button>
            <a href="{{ route('tasks.index', [$property, $room]) }}" class="px-3 py-2 rounded border">Cancel</a>
        </div>
    </form>
</x-app-layout>
