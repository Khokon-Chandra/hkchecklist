@php
    use Illuminate\Support\Str;
@endphp

@props([
    'task',
    'item' => null,
    'session',
    'room' => null,
    'disabled' => false,
    'completed' => false,
])

@php
    $isPropertyTask = $room === null;
    $toggleRoute = $isPropertyTask
        ? route('checklist.property-task.toggle', [$session, $task])
        : route('checklist.toggle', [$session, $room, $task]);
    $noteRoute = $isPropertyTask
        ? route('checklist.property-task.note', [$session, $task])
        : route('checklist.note', [$session, $room, $task]);

    // Get instructions from pivot (room-specific) or task model
    $instructions = null;
    if (!$isPropertyTask && isset($task->pivot) && !empty($task->pivot->instructions)) {
        $instructions = $task->pivot->instructions;
    } elseif (!empty($task->instructions)) {
        $instructions = $task->instructions;
    }

    $hasMedia = method_exists($task, 'media') && $task->media->count() > 0;
    $hasInstructions = !empty($instructions);
    $showDetails = $hasMedia || $hasInstructions;

    // Auto-expand details for unchecked tasks so users can see instructions before checking
    $autoExpandDetails = !$completed && $showDetails;
@endphp

<div
    data-task-item
    class="group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 {{ $completed ? 'opacity-90' : '' }}"
    x-data="{
        detailsOpen: {{ $autoExpandDetails ? 'true' : 'false' }},
        galleryOpen: false,
        gallerySrc: null,
        noteValue: '{{ $item?->note ?? '' }}',
        noteSaving: false
    }"
>
    <div class="p-4">
        <div class="flex items-start gap-4">
            {{-- Toggle Checkbox --}}
            <div class="flex-shrink-0 pt-0.5">
                <button
                    type="button"
                    data-checklist-toggle
                    data-toggle-url="{{ $toggleRoute }}"
                    data-checked="{{ $completed ? 'true' : 'false' }}"
                    class="relative w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:scale-110' }} {{ $completed ? 'bg-green-600 border-green-600 text-white' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600' }}"
                    {{ $disabled ? 'disabled' : '' }}
                    aria-label="{{ $completed ? 'Mark as incomplete' : 'Mark as complete' }}"
                >
                    @if($completed)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @endif
                </button>
            </div>

            {{-- Task Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h3
                            data-task-name
                            class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1 transition-all {{ $completed ? 'line-through text-gray-500 dark:text-gray-400' : '' }}"
                        >
                            {{ $task->name }}
                        </h3>

                        @if($showDetails)
                            <div class="flex items-center gap-2 mt-1">
                                <button
                                    type="button"
                                    @click="detailsOpen = !detailsOpen"
                                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors font-medium"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': detailsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span x-text="detailsOpen ? 'Hide Instructions' : 'View Instructions'"></span>
                                </button>
                                @if($hasMedia)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        • {{ $task->media->count() }} {{ Str::plural('media', $task->media->count()) }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($hasInstructions && !$completed)
                            {{-- Show instruction preview for unchecked tasks --}}
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
                                {{ Str::limit(strip_tags($instructions), 120) }}
                            </p>
                        @endif
                    </div>

                    {{-- Note Input --}}
                    <div class="flex-shrink-0" data-note-container>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                data-note-input
                                x-model="noteValue"
                                placeholder="Add note..."
                                @keydown.enter.prevent="
                                    const btn = $el.nextElementSibling;
                                    if (btn && !btn.disabled) btn.click();
                                "
                                class="w-48 px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $disabled ? 'readonly' : '' }}
                            />
                            <button
                                type="button"
                                data-checklist-note-save="true"
                                data-note-url="{{ $noteRoute }}"
                                data-note-saving="false"
                                {{ $disabled ? 'disabled' : '' }}
                                class="px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Collapsible Details --}}
                <div
                    x-show="detailsOpen"
                    x-collapse
                    x-cloak
                    class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                >
                    @if($hasInstructions)
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Instructions:</h4>
                            <div class="prose dark:prose-invert prose-sm max-w-none bg-gray-50 dark:bg-gray-900/40 rounded-lg p-4">
                                {!! nl2br(e($instructions)) !!}
                            </div>
                        </div>
                    @endif

                    @if($hasMedia)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($task->media as $media)
                                <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 group">
                                    @if($media->type === 'image')
                                        <button
                                            type="button"
                                            @click="galleryOpen = true; gallerySrc = '{{ $media->url }}'"
                                            class="block w-full"
                                        >
                                            <img
                                                src="{{ $media->thumbnail ?? $media->url }}"
                                                alt="{{ $media->caption ?? 'Task media' }}"
                                                class="w-full h-32 object-cover transition-transform group-hover:scale-105"
                                                loading="lazy"
                                            />
                                        </button>
                                    @else
                                        <video
                                            src="{{ $media->url }}"
                                            class="w-full h-32 object-cover"
                                            controls
                                            muted
                                        ></video>
                                    @endif
                                    @if($media->caption)
                                        <span class="absolute bottom-1 left-1 text-xs px-2 py-1 rounded bg-black/60 text-white">
                                            {{ Str::limit($media->caption, 20) }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Image Gallery Modal --}}
    <div
        x-show="galleryOpen"
        x-cloak
        @click.self="galleryOpen = false"
        @keydown.escape.window="galleryOpen = false"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4"
    >
        <img
            :src="gallerySrc"
            class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl"
            alt="Gallery view"
        />
        <button
            type="button"
            @click="galleryOpen = false"
            class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 transition-colors"
        >
            ×
        </button>
    </div>
</div>
