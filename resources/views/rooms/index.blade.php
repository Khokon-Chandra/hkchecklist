{{-- resources/views/rooms/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl flex items-center gap-2">
            Rooms
        </h2>
    </x-slot>

    <div class="flex items-center justify-end mb-4 gap-2">
        <x-button variant="primary" href="{{ route('rooms.create') }}">+ Add Room</x-button>
    </div>

    <x-card class="!px-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="uppercase text-xs tracking-wide">
                    <tr class="text-gray-600 dark:text-gray-300">
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-center">Default?</th>
                        <th class="px-4 py-2 text-center">Tasks</th>
                        <th class="px-4 py-2 text-center">Created</th>
                        <th class="px-4 py-2 w-40 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($rooms as $r)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $r->name }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-xs
                                    {{ $r->is_default
                                        ? 'bg-green-100 text-green-800 dark:bg-green-400/20 dark:text-green-300'
                                        : 'bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $r->is_default ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $r->tasks_count ?? $r->tasks()->count() }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $r->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @role('admin|owner')
                                    <a class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        href="{{ route('rooms.edit', $r) }}">Edit</a>

                                    <form action="{{ route('rooms.destroy', $r) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="ml-4 text-red-600 hover:underline dark:text-red-400"
                                            onclick="return confirm('Are you sure you want to delete this room?');">
                                            Delete
                                        </button>
                                    </form>
                                @endrole

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-500 dark:text-gray-400" colspan="6">
                                No rooms yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($rooms, 'links'))
            <div class="px-4 py-3">
                {{ $rooms->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
