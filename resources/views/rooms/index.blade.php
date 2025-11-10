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
                        <th class="px-4 py-2 text-left">#</th>
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
                            <td class="px-4 py-3 text-left">{{ ($rooms->firstItem() ?? 0) + $loop->index }}</td>
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

                                <x-action-dropdown align="right" width="w-56" label="Room actions">
                                    <x-dropdown.item as="button"
                                        x-on:click="
                                            $dispatch('open-modal', 'assign-tasks-{{ $r->id }}');
                                            $root.closest('[x-data]')?.__x?.$data?.close?.();
                                        ">
                                        {{-- plus icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z" />
                                        </svg>
                                        <span>Assign Tasks</span>
                                    </x-dropdown.item>



                                    <x-dropdown.item
                                        x-on:click="
                                            $dispatch('open-modal', 'assign-tasks-{{ $r->id }}');
                                            $root.closest('[x-data]')?.__x?.$data?.close?.();
                                        ">
                                        <form action="{{ route('rooms.update', $r->id) }}" method="POST"
                                            class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="name" value="{{ $r->name }}" />
                                            <input type="hidden" name="assign_defaults" value="1" />
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path
                                                    d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z" />
                                            </svg>
                                            <button type="submit" class="text-sm inline">Assign Default Tasks</button>
                                        </form>

                                    </x-dropdown.item>



                                    <x-dropdown.item href="{{ route('tasks.index', ['room_id' => $r->id]) }}">
                                        {{-- edit icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M3 7a2 2 0 0 1 2-2h4v14H5a2 2 0 0 1-2-2V7zm12-2h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4V5zM9 5h6v14H9V5z" />
                                        </svg>
                                        <span>Tasks</span>
                                    </x-dropdown.item>

                                    <x-dropdown.item href="{{ route('rooms.edit', $r) }}">
                                        {{-- edit icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11zM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83z" />
                                        </svg>
                                        <span>Edit</span>
                                    </x-dropdown.item>


                                    <x-dropdown.item as="form" method="POST"
                                        href="{{ route('rooms.destroy', $r) }}">
                                        @method('DELETE')
                                        <button type="submit" data-menu-item
                                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-900/20 rounded">
                                            {{-- trash icon --}}
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path
                                                    d="M9 3a1 1 0 0 0-1 1v1H4a1 1 0 1 0 0 2h1v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7h1a1 1 0 1 0 0-2h-4V4a1 1 0 0 0-1-1H9zm2 4a1 1 0 1 0-2 0v10a1 1 0 1 0 2 0V7zm4 0a1 1 0 1 0-2 0v10a1 1 0 1 0 2 0V7z" />
                                            </svg>
                                            <span>Delete</span>
                                        </button>
                                    </x-dropdown.item>
                                </x-action-dropdown>

                                {{-- Modal instance per row --}}
                                <x-assign-tasks :room="$r" />
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
