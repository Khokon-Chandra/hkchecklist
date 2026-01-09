<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl">
                    Property Tasks — {{ $property->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Manage property-level tasks that happen before, during, or after cleaning (not room-specific).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-button variant="secondary" href="{{ route('properties.rooms.index', $property) }}">← Rooms</x-button>
                <x-button variant="primary" @click="$dispatch('open-preview-panel', 'add-property-task-{{ $property->id }}')">
                    + Add Property Task
                </x-button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Pre-Cleaning Tasks --}}
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Pre-Cleaning Tasks
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tasks to complete before starting room cleaning (e.g., check inventory, verify supplies).
                </p>
            </div>

            @php
                $preTasks = $property->propertyTasks()
                    ->where('phase', 'pre_cleaning')
                    ->orderBy('property_tasks.sort_order')
                    ->get();
            @endphp

            @if($preTasks->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 py-4">No pre-cleaning tasks yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($preTasks as $task)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $task->name }}</div>
                                @if($task->pivot->instructions)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $task->pivot->instructions }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        @click="$dispatch('open-preview-panel', 'edit-property-task-{{ $property->id }}-{{ $task->id }}')">
                                    Edit
                                </button>
                                <span class="text-gray-400">·</span>
                                <form action="{{ route('properties.property-tasks.detach', [$property, $task]) }}" method="post" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline dark:text-rose-400"
                                            onclick="return confirm('Remove this task from the property?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- During-Cleaning Tasks --}}
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    During-Cleaning Tasks
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tasks to complete throughout the cleaning process (e.g., check whole place, monitor progress).
                </p>
            </div>

            @php
                $duringTasks = $property->propertyTasks()
                    ->where('phase', 'during_cleaning')
                    ->orderBy('property_tasks.sort_order')
                    ->get();
            @endphp

            @if($duringTasks->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 py-4">No during-cleaning tasks yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($duringTasks as $task)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $task->name }}</div>
                                @if($task->pivot->instructions)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $task->pivot->instructions }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        @click="$dispatch('open-preview-panel', 'edit-property-task-{{ $property->id }}-{{ $task->id }}')">
                                    Edit
                                </button>
                                <span class="text-gray-400">·</span>
                                <form action="{{ route('properties.property-tasks.detach', [$property, $task]) }}" method="post" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline dark:text-rose-400"
                                            onclick="return confirm('Remove this task from the property?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Post-Cleaning Tasks --}}
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Post-Cleaning Tasks
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tasks to complete after room cleaning is done (e.g., final inspection, whole place check).
                </p>
            </div>

            @php
                $postTasks = $property->propertyTasks()
                    ->where('phase', 'post_cleaning')
                    ->orderBy('property_tasks.sort_order')
                    ->get();
            @endphp

            @if($postTasks->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 py-4">No post-cleaning tasks yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($postTasks as $task)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $task->name }}</div>
                                @if($task->pivot->instructions)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $task->pivot->instructions }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        @click="$dispatch('open-preview-panel', 'edit-property-task-{{ $property->id }}-{{ $task->id }}')">
                                    Edit
                                </button>
                                <span class="text-gray-400">·</span>
                                <form action="{{ route('properties.property-tasks.detach', [$property, $task]) }}" method="post" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline dark:text-rose-400"
                                            onclick="return confirm('Remove this task from the property?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    {{-- Add Property Task Preview Panel --}}
    <x-preview-panel
        name="add-property-task-{{ $property->id }}"
        :overlay="true"
        side="right"
        initialWidth="32rem"
        minWidth="24rem"
        title="Add Property Task"
        :subtitle="'Add a property-level task to ' . $property->name">

        @php $suggestUrl = route('tasks.suggest'); @endphp

        @include('properties.property-tasks.__property_task_form', [
            'property' => $property,
            'task' => null,
            'pivot' => null,
            'suggestUrl' => $suggestUrl,
            'mode' => 'create',
        ])
    </x-preview-panel>

    {{-- Edit Property Task Preview Panels --}}
    @php
        $allPropertyTasks = $property->propertyTasks()->get();
    @endphp
    @foreach($allPropertyTasks as $task)
        <x-preview-panel
            name="edit-property-task-{{ $property->id }}-{{ $task->id }}"
            :overlay="true"
            side="right"
            initialWidth="32rem"
            minWidth="24rem"
            title="Edit Property Task"
            :subtitle="$task->name">

            @php
                $suggestUrl = route('tasks.suggest');
                $pivot = $task->pivot;
            @endphp

            @include('properties.property-tasks.__property_task_form', [
                'property' => $property,
                'task' => $task,
                'pivot' => $pivot,
                'suggestUrl' => $suggestUrl,
                'mode' => 'edit',
            ])
        </x-preview-panel>
    @endforeach
</x-app-layout>

