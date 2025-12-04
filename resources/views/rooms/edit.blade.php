{{-- resources/views/rooms/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Edit Room & Tasks
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update the room details and which tasks are attached, all in one place.
                </p>
            </div>

            <x-button variant="secondary" href="{{ route('rooms.index') }}">
                Back to Rooms
            </x-button>
        </div>
    </x-slot>

    <x-card>
        <form method="POST" action="{{ route('rooms.update', $room) }}" x-data="roomTasksEditor" x-init="init()"
            data-tasks='@json($tasks)' data-selected-task-ids='@json($room->tasks->pluck('id'))'>
            @csrf
            @method('PUT')

            {{-- Room details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <x-form.label value="Room Name" />
                    <x-form.input name="name" class="w-full" required value="{{ old('name', $room->name) }}" />
                    <x-form.error :messages="$errors->get('name')" />
                </div>

                <div class="flex items-center gap-3 mt-6 md:mt-8">
                    <input type="checkbox" id="is_default" name="is_default" value="1"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('is_default', $room->is_default)) />
                    <label for="is_default" class="text-sm text-gray-700 dark:text-gray-300">
                        Mark as default room type
                    </label>
                </div>
            </div>

            {{-- Tasks section --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Tasks for this room
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Attach or detach tasks. These tasks will appear for this room in cleaning sessions.
                        </p>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 text-right space-y-0.5">
                        <p>
                            <span class="font-semibold" x-text="selectedTaskIds.length"></span>
                            task(s) selected
                        </p>
                        <p>
                            <span x-text="filteredTasks().length"></span>
                            task(s) visible with current filters
                        </p>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center mb-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search tasks
                        </label>
                        <input type="text"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
                            placeholder="Type to filter by name…" x-model="search" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Type
                        </label>
                        <x-form.select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm"
                            x-model="typeFilter">
                            <option value="">All types</option>
                            <option value="room">Room</option>
                            <option value="inventory">Inventory</option>
                        </x-form.select>
                    </div>

                    <button type="button"
                        class="mt-4 items-end px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        @click="toggleSelectAllVisible()">
                        Select all visible
                    </button>
                </div>

                {{-- Task list --}}
                <div
                    class="max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg divide-y dark:divide-gray-700">
                    <template x-if="filteredTasks().length === 0">
                        <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                            No tasks match your filters.
                        </div>
                    </template>

                    <template x-for="task in filteredTasks()" :key="task.id">
                        <label
                            class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800/70 cursor-pointer text-sm">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600"
                                    :value="task.id" x-model="selectedTaskIds" name="task_ids[]" />

                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="task.name"></div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        <span class="uppercase tracking-wide" x-text="task.type"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="task.is_default ? 'Default task' : 'Custom task'"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wide
                                        bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                                    x-text="task.type"></span>

                                <span class="px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wide"
                                    :class="task.is_default ?
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' :
                                        'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200'">
                                    <span x-text="task.is_default ? 'Default' : 'Custom'"></span>
                                </span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-8 flex items-center justify-end gap-2">
                <x-button variant="secondary" href="{{ route('rooms.index') }}">
                    Cancel
                </x-button>

                <x-button type="submit">
                    Save Room & Tasks
                </x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
