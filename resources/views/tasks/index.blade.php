<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Tasks — {{ $room->name }} ({{ $property->name }})</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-4">
        <x-button variant="secondary" href="{{ route('rooms.index', $property) }}">←
            Back to Rooms</x-button>
        <x-button href="{{ route('tasks.create', [$property, $room]) }}">+ Add
            Task</x-button>
    </div>

    <x-card class="mb-4 !px-0">
        <table class="min-w-full text-sm">
            <thead class="uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Task</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Default?</th>
                    <th class="px-4 py-2">Created at</th>
                    @role('admin|owner')
                        <th class="px-4 py-2 w-36 text-right">Action</th>
                    @endrole
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($tasks as $t)
                    <tr>
                        <td class="px-4 py-2">{{ $t->name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-900">{{ ucfirst($t->type) }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-0.5 rounded text-xs {{ $t->is_default ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800 dark:text-gray-400 dark:bg-gray-900' }}">
                                {{ $t->is_default ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">{{ $t->created_at }}</td>
                        @role('admin|owner')
                            <td class="px-4 py-2 text-right">
                                <a class="text-indigo-600 hover:underline"
                                    href="{{ route('tasks.edit', [$property, $room, $t]) }}">Edit</a>
                            </td>
                        @endrole
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="4">No tasks yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-app-layout>
