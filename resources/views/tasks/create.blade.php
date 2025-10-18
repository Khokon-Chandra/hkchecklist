<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Task — {{ $room->name }}</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('tasks.store', [$property, $room]) }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <x-form.label value="Task" />
                    <x-form.input name="name" class="w-full" required />
                </div>
                <div>
                    <x-form.label value="Type" />
                    <x-form.select name="type" class="w-full rounded border-gray-300">
                        <option value="room">Room</option>
                        <option value="inventory">Inventory</option>
                    </x-form.select>
                </div>
                <div class="flex items-center gap-2">
                    <input id="isDefault" type="checkbox" name="is_default" value="1"
                        class="rounded border-gray-300">
                    <label for="isDefault">Default template</label>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button>Save</x-button>
                <x-button variant="danger" href="{{ route('tasks.index', [$property, $room]) }}">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
