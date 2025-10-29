<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Add Room — {{ $property->name }}</h2>
    </x-slot>
    <x-card>
        <form method="post" action="{{ route('rooms.store', $property) }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><x-form.label value="Name" /><x-form.input name="name" class="w-full" required /></div>
                <div class="flex items-center gap-2 pt-6">
                    <input id="is_default" type="checkbox" name="is_default" value="1"
                        class="rounded border-gray-300">
                    <label for="is_default">Mark as default template</label>
                </div>
            </div>
            <div class="flex gap-2">
                <x-button>Save</x-button>
                <x-button variant="secondary" href="{{ route('rooms.index', $property) }}"
                    class="px-3 py-2 rounded border">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
