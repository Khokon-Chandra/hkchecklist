<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl flex items-center gap-2">
            Tasks
        </h2>
    </x-slot>

    {{-- Toolbar: search + type filter + create --}}
    <div class="mb-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <form method="get" action="{{ route('tasks.index') }}" class="flex-1 flex items-center gap-2">
            <x-form.input name="q" placeholder="Search tasks…" value="{{ request('q') }}" class="w-full" />

            <x-form.select name="type" class="w-40 !py-1">
                <option value="">All types</option>
                <option value="room" @selected(request('type') === 'room')>Room</option>
                <option value="inventory" @selected(request('type') === 'inventory')>Inventory</option>
            </x-form.select>

            <x-button type="submit" class="whitespace-nowrap">Filter</x-button>

            @if (request()->hasAny(['q', 'type']))
                <a href="{{ route('tasks.index') }}"
                    class="text-sm underline text-gray-600 dark:text-gray-300">Reset</a>
            @endif
        </form>

        <x-button variant="primary" href="{{ route('tasks.create') }}" class="whitespace-nowrap">
            + New Task
        </x-button>
    </div>

    <x-card class="!px-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="uppercase text-xs tracking-wide">
                    <tr class="text-gray-600 dark:text-gray-300">
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-center">Type</th>
                        <th class="px-4 py-2 text-center">Default?</th>
                        <th class="px-4 py-2 text-center">Created</th>
                        <th class="px-4 py-2 w-40 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($tasks as $t)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $t->name }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-xs
                                    {{ $t->type === 'inventory'
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-400/20 dark:text-blue-300'
                                        : 'bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ ucfirst($t->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-xs
                                    {{ $t->is_default
                                        ? 'bg-green-100 text-green-800 dark:bg-green-400/20 dark:text-green-300'
                                        : 'bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $t->is_default ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $t->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a class="text-indigo-600 hover:underline dark:text-indigo-400"
                                    href="{{ route('tasks.edit', $t) }}">Edit</a>

                                <span class="mx-2 text-gray-400">·</span>

                                {{-- Row-level delete form (not nested in another form) --}}
                                <form method="post" action="{{ route('tasks.destroy', $t) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline"
                                        onclick="return confirm('Delete this task?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-10 text-center text-gray-500 dark:text-gray-400" colspan="5">
                                No tasks found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($tasks, 'links'))
            <div class="px-4 py-3">
                {{ $tasks->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
