<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Tasks — {{ $room->name }} ({{ $property->name }})</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('rooms.index', $property) }}" class="text-gray-600 hover:underline">← Back to Rooms</a>
        <a href="{{ route('tasks.create', [$property, $room]) }}" class="px-3 py-2 rounded bg-indigo-600 text-white">+ Add
            Task</a>
    </div>

    <div class="overflow-hidden rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Task</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Default?</th>
                    <th class="px-4 py-2 w-36"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tasks as $t)
                    <tr>
                        <td class="px-4 py-2">{{ $t->name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-0.5 rounded text-xs bg-gray-100">{{ ucfirst($t->type) }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-0.5 rounded text-xs {{ $t->is_default ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $t->is_default ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a class="text-indigo-600 hover:underline"
                                href="{{ route('tasks.edit', [$property, $room, $t]) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="4">No tasks yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
