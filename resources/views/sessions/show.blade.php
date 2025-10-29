<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Checklist — {{ $session->property->name }} ({{ $session->scheduled_date->toDateString() }})
        </h2>
    </x-slot>




    <!-- Session start gate: only visible when session status is pending -->
    @if ($session->status === 'pending')
        <x-card class="p-6 rounded border dark:border-gray-700 bg-white dark:bg-gray-800">
            <p class="mb-3 text-gray-700 dark:text-gray-300">
                You can start now. GPS is optional—if your device provides a location we'll attach it automatically.
            </p>
            <form method="post" action="{{ route('sessions.start', $session) }}" id="gps-start"
                class="flex flex-col sm:flex-row sm:items-center gap-2">
                @csrf
                <x-form.input type="hidden" name="latitude" id="lat" />
                <x-form.input type="hidden" name="longitude" id="lng" />
                <div class="flex items-center gap-2">
                    <x-button>Start Session</x-button>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        If location is available it will be attached automatically.
                    </span>
                </div>
            </form>
            @error('gps')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                If your browser blocks location, the session will still start without GPS.
            </p>
        </x-card>

        <!-- Attempt to capture device GPS coordinates but do not block if unavailable -->
        <script>
            navigator.geolocation?.getCurrentPosition(
                function(position) {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('lng').value = position.coords.longitude;
                },
                function() {
                    // Silent fail—GPS is optional
                }, {
                    enableHighAccuracy: true,
                    timeout: 4000,
                }
            );
        </script>
    @else
        <!-- Progress header when session is in progress or completed -->
        <x-card
            class="border dark:border-gray-700 mb-4 flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-2 sm:mb-0">
                <x-status-badge :status="$session->status" />
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    Started: {{ optional($session->started_at)->format('Y-m-d H:i') ?? '—' }}
                </span>
                @if ($session->gps_confirmed_at)
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">GPS
                        Confirmed</span>
                @else
                    <span
                        class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">GPS
                        Not Captured</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-300">Stage:</span>
                <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-900">
                    {{ strtoupper($stage) }}
                </span>
            </div>
        </x-card>

        <!-- Rooms stage: tasks must be completed room-by-room -->
        @if ($stage === 'rooms')
            <div class="space-y-6">
                @foreach ($rooms as $index => $room)
                    @php
                        $tasks = $roomTasksByRoom[$room->id] ?? collect();
                        // Determine if this room is disabled (we enforce sequential completion)
                        $disabled = $firstIncompleteRoomIndex !== null && $index > $firstIncompleteRoomIndex;
                    @endphp
                    <x-card>
                        <div class="px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $room->name }}</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $tasks->count() }} task{{ $tasks->count() === 1 ? '' : 's' }}
                            </span>
                        </div>
                        <ul class="divide-y dark:divide-gray-700">
                            @forelse ($tasks as $task)
                                @php $item = $session->checklistItems->firstWhere('task_id', $task->id); @endphp
                                <li class="px-4 py-3 flex items-start sm:items-center justify-between gap-3">
                                    <div class="flex items-start sm:items-center gap-3">
                                        <form method="post" action="{{ route('checklist.toggle', [$session, $task]) }}"
                                            class="flex-shrink-0">
                                            @csrf
                                            @php
                                                // Build button classes server-side to avoid nested Blade/JS expressions
                                                $btnClasses =
                                                    'h-5 w-5 rounded border flex items-center justify-center transition-colors';
                                                if ($disabled) {
                                                    $btnClasses .= ' opacity-50 cursor-not-allowed';
                                                }
                                                if ($item && $item->checked) {
                                                    $btnClasses .= ' bg-green-600 border-green-600 text-white';
                                                } else {
                                                    $btnClasses .=
                                                        ' bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-300';
                                                }
                                            @endphp
                                            <button class="{{ $btnClasses }}"
                                                @if ($disabled) disabled @endif>
                                                @if ($item?->checked)
                                                    ✓
                                                @endif
                                            </button>
                                        </form>
                                        <span
                                            class="flex-1 text-sm {{ $item?->checked ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200' }}">
                                            {{ $task->name }}
                                        </span>
                                    </div>
                                    <form method="post" action="{{ route('checklist.note', [$session, $task]) }}"
                                        class="flex items-center gap-2">
                                        @csrf
                                        <x-form.input name="note" value="{{ $item?->note }}" placeholder="Note"
                                            class="w-full md:w-auto rounded border-gray-300 dark:border-gray-600 text-sm dark:bg-gray-700 dark:text-gray-200"
                                            @if ($disabled) readonly @endif />
                                        @php
                                            // Determine if the secondary button should be disabled
                                            $saveDisabled = $disabled;
                                        @endphp
                                        <x-button variant="secondary" :disabled="$saveDisabled">Save</x-button>
                                    </form>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">No tasks defined for
                                    this room.</li>
                            @endforelse
                        </ul>
                    </x-card>
                @endforeach
            </div>
        @endif

        <!-- Inventory stage: tasks become available after all room tasks are complete -->
        @if ($stage === 'inventory')
            <div class="space-y-6">
                @foreach ($rooms as $index => $room)
                    @php
                        $tasks = $inventoryTasksByRoom[$room->id] ?? collect();
                        $disabled = $firstIncompleteInventoryIndex !== null && $index > $firstIncompleteInventoryIndex;
                    @endphp
                    @if ($tasks->count())
                        <x-card>
                            <div class="px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $room->name }} —
                                    Inventory</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $tasks->count() }} item{{ $tasks->count() === 1 ? '' : 's' }}
                                </span>
                            </div>
                            <ul class="divide-y dark:divide-gray-700">
                                @foreach ($tasks as $task)
                                    @php $item = $session->checklistItems->firstWhere('task_id', $task->id); @endphp
                                    <li class="px-4 py-3 flex items-start sm:items-center justify-between gap-3">
                                        <div class="flex items-start sm:items-center gap-3">
                                            <form method="post"
                                                action="{{ route('checklist.toggle', [$session, $task]) }}"
                                                class="flex-shrink-0">
                                                @csrf
                                                @php
                                                    // Build inventory button classes server-side
                                                    $invBtnClasses =
                                                        'px-2 py-1 rounded border text-sm transition-colors';
                                                    if ($disabled) {
                                                        $invBtnClasses .= ' opacity-50 cursor-not-allowed';
                                                    }
                                                    if ($item && $item->checked) {
                                                        $invBtnClasses .= ' bg-green-600 text-white border-green-600';
                                                    } else {
                                                        $invBtnClasses .=
                                                            ' bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600';
                                                    }
                                                @endphp
                                                <button class="{{ $invBtnClasses }}"
                                                    @if ($disabled) disabled @endif>
                                                    {{ $item?->checked ? '✓' : 'Mark' }}
                                                </button>
                                            </form>
                                            <span
                                                class="flex-1 text-sm {{ $item?->checked ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200' }}">
                                                {{ $task->name }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Photos stage: show per-room upload and gallery; allow completion only when each room has ≥8 photos -->
        @if ($stage === 'photos')
            <div class="space-y-6">
                @foreach ($rooms as $room)
                    @php
                        $roomPhotos = $photosByRoom[$room->id] ?? collect();
                    @endphp
                    <x-card x-data="{ open: false, activeSrc: null }">
                        <div class="px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $room->name }} — Photos
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $photoCounts[$room->id] ?? 0 }}/8 photos
                            </span>
                        </div>
                        <div class="p-4">
                            <form method="post" enctype="multipart/form-data"
                                action="{{ route('photos.store', [$session, $room->id]) }}"
                                class="flex items-center gap-2 mb-4">
                                @csrf
                                <x-form.input type="file" name="photos[]" multiple accept="image/*"
                                    class="rounded border-gray-300 dark:border-gray-600 text-sm dark:bg-gray-700 dark:text-gray-200" />
                                <x-button>Upload</x-button>
                            </form>
                            @if ($roomPhotos->count())
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach ($roomPhotos as $photo)
                                        @php
                                            $src = \Illuminate\Support\Str::startsWith($photo->path, [
                                                'http://',
                                                'https://',
                                            ])
                                                ? $photo->path
                                                : asset('storage/' . $photo->path);
                                        @endphp
                                        <button type="button" class="group relative"
                                            @click="open = true; activeSrc='{{ $src }}'">
                                            <img src="{{ $src }}" alt="Photo"
                                                class="aspect-square w-full object-cover rounded-xl border" />
                                            <span
                                                class="absolute bottom-1 right-1 text-[10px] px-1.5 py-0.5 rounded bg-black/60 text-white">
                                                {{ optional($photo->captured_at)->format('H:i') }}
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">No photos yet.</p>
                            @endif
                            <div x-show="open" x-cloak
                                class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4"
                                @keydown.escape.window="open = false" @click.self="open = false">
                                <img :src="activeSrc" class="max-h-[85vh] rounded-xl shadow-xl" alt="Preview">
                                <button type="button" class="absolute top-4 right-4 text-white text-2xl"
                                    @click="open=false">×</button>
                            </div>
                        </div>
                    </x-card>
                @endforeach
                <x-card>
                    <form method="post" action="{{ route('sessions.complete', $session) }}" class="text-center">
                        @csrf
                        <x-button>Submit Checklist</x-button>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Requires ≥8 photos per room. Timestamp overlay is automatic on upload.
                        </p>
                    </form>
                </x-card>
            </div>
        @endif

        <!-- Summary stage: show all tasks and allow edits after completion -->
        @if ($stage === 'summary')
            <div class="space-y-6">
                @foreach ($rooms as $room)
                    @php
                        $roomTasks = $roomTasksByRoom[$room->id] ?? collect();
                        $inventoryTasks = $inventoryTasksByRoom[$room->id] ?? collect();
                    @endphp
                    <x-card>
                        <div class="px-4 py-3 border-b dark:border-gray-700">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $room->name }}</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @if ($roomTasks->count())
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">Room
                                        Tasks</h4>
                                    <ul class="divide-y dark:divide-gray-700">
                                        @foreach ($roomTasks as $task)
                                            @php $item = $session->checklistItems->firstWhere('task_id', $task->id); @endphp
                                            <li class="py-2 flex items-start sm:items-center justify-between gap-3">
                                                <div class="flex items-start sm:items-center gap-3">
                                                    <form method="post"
                                                        action="{{ route('checklist.toggle', [$session, $task]) }}"
                                                        class="flex-shrink-0">
                                                        @csrf
                                                        @php
                                                            // Build summary room button classes
                                                            $summaryRoomBtn =
                                                                'h-5 w-5 rounded border flex items-center justify-center transition-colors';
                                                            if ($item && $item->checked) {
                                                                $summaryRoomBtn .=
                                                                    ' bg-green-600 border-green-600 text-white';
                                                            } else {
                                                                $summaryRoomBtn .=
                                                                    ' bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-300';
                                                            }
                                                        @endphp
                                                        <button class="{{ $summaryRoomBtn }}">
                                                            @if ($item?->checked)
                                                                ✓
                                                            @endif
                                                        </button>
                                                    </form>
                                                    <span
                                                        class="flex-1 text-sm {{ $item?->checked ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200' }}">
                                                        {{ $task->name }}
                                                    </span>
                                                </div>
                                                <form method="post"
                                                    action="{{ route('checklist.note', [$session, $task]) }}"
                                                    class="flex items-center gap-2">
                                                    @csrf
                                                    <x-form.input name="note" value="{{ $item?->note }}"
                                                        placeholder="Note"
                                                        class="w-full md:w-auto rounded border-gray-300 dark:border-gray-600 text-sm dark:bg-gray-700 dark:text-gray-200" />
                                                    <x-button variant="secondary">Save</x-button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if ($inventoryTasks->count())
                                <div>
                                    <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">
                                        Inventory Tasks</h4>
                                    <ul class="divide-y dark:divide-gray-700">
                                        @foreach ($inventoryTasks as $task)
                                            @php $item = $session->checklistItems->firstWhere('task_id', $task->id); @endphp
                                            <li class="py-2 flex items-start sm:items-center justify-between gap-3">
                                                <div class="flex items-start sm:items-center gap-3">
                                                    <form method="post"
                                                        action="{{ route('checklist.toggle', [$session, $task]) }}"
                                                        class="flex-shrink-0">
                                                        @csrf
                                                        @php
                                                            // Build summary inventory button classes
                                                            $summaryInvBtn =
                                                                'px-2 py-1 rounded border text-sm transition-colors';
                                                            if ($item && $item->checked) {
                                                                $summaryInvBtn .=
                                                                    ' bg-green-600 text-white border-green-600';
                                                            } else {
                                                                $summaryInvBtn .=
                                                                    ' bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600';
                                                            }
                                                        @endphp
                                                        <button class="{{ $summaryInvBtn }}">
                                                            {{ $item?->checked ? '✓' : 'Mark' }}
                                                        </button>
                                                    </form>
                                                    <span
                                                        class="flex-1 text-sm {{ $item?->checked ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200' }}">
                                                        {{ $task->name }}
                                                    </span>
                                                </div>
                                                <form method="post"
                                                    action="{{ route('checklist.note', [$session, $task]) }}"
                                                    class="flex items-center gap-2">
                                                    @csrf
                                                    <x-form.input name="note" value="{{ $item?->note }}"
                                                        placeholder="Note"
                                                        class="w-full md:w-auto rounded border-gray-300 dark:border-gray-600 text-sm dark:bg-gray-700 dark:text-gray-200" />
                                                    <x-button variant="secondary">Save</x-button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endforeach
                <!-- Summary of photos -->
                <x-card>
                    <div class="px-4 py-3 border-b dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Photos Summary</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach ($rooms as $room)
                            <div>
                                <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $room->name }}: {{ $photoCounts[$room->id] ?? 0 }}/8 photos</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach ($photosByRoom[$room->id] ?? collect() as $photo)
                                        @php
                                            $src = \Illuminate\Support\Str::startsWith($photo->path, [
                                                'http://',
                                                'https://',
                                            ])
                                                ? $photo->path
                                                : asset('storage/' . $photo->path);
                                        @endphp
                                        <img src="{{ $src }}" alt="Photo"
                                            class="aspect-square w-full object-cover rounded-xl border" />
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        @endif
    @endif



</x-app-layout>
