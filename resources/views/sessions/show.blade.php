<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Checklist — {{ $session->property->name }} ({{ $session->scheduled_date->toDateString() }})
        </h2>
    </x-slot>

    {{-- Start gate --}}
    @if ($session->status === 'pending')
        <x-card class="p-6 rounded border">
            <p class="mb-3 text-gray-700 dark:text-gray-400">GPS confirmation required to start.</p>
            <form method="post" action="{{ route('sessions.start', $session) }}" id="gps-start"
                class="flex items-center gap-2">
                @csrf
                <x-form.input type="hidden" name="latitude" id="lat" />
                <x-form.input type="hidden" name="longitude" id="lng" />
                <x-button>Start Session</x-button>
            </form>
            <p class="mt-2 text-xs text-gray-500">Enable location in your browser and try again if it fails.</p>
        </x-card>
        <script>
            navigator.geolocation?.getCurrentPosition(p => {
                document.getElementById('lat').value = p.coords.latitude;
                document.getElementById('lng').value = p.coords.longitude;
            }, () => alert('GPS permission required to start'));
        </script>
    @else
        {{-- Progress header --}}
        <x-card class="border dark:border-gray-600 mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-status-badge :status="$session->status" />
                <span class="text-sm text-gray-600">Started:
                    {{ optional($session->started_at)->format('Y-m-d H:i') ?? '—' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-300">Stage:</span>
                <span class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-900">{{ strtoupper($stage) }}</span>
            </div>
        </x-card>

        {{-- Rooms checklist --}}
        @if ($roomTasksByRoom)
            <div class="space-y-6">
                @foreach ($roomTasksByRoom as $room)
                    <x-card>
                        <div class="px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold">{{ $room->name }}</h3>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-300">{{ $room->tasks->where('type', 'room')->count() }}
                                tasks</span>
                        </div>
                        <ul class="divide-y dark:divide-gray-700">
                            @foreach ($room->tasks->where('type', 'room') as $task)
                                @php $item = $session->checklistItems->firstWhere('task_id',$task->id); @endphp
                                <li class="px-4 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <form method="post"
                                            action="{{ route('checklist.toggle', [$session, $item]) }}">
                                            @csrf
                                            <button
                                                class="h-5 w-5 rounded border flex items-center justify-center {{ $item?->checked ? 'bg-green-600 border-green-600 text-white' : 'bg-white' }}">
                                                @if ($item?->checked)
                                                    ✓
                                                @endif
                                            </button>
                                        </form>
                                        <span
                                            class="{{ $item?->checked ? 'line-through text-gray-500' : '' }}">{{ $task->name }}</span>
                                    </div>
                                    <form method="post" action="{{ route('checklist.note', [$session, $item]) }}"
                                        class="flex items-center gap-2">
                                        @csrf
                                        <x-form.input name="note" value="{{ $item?->note }}" placeholder="Note"
                                            class="rounded border-gray-300 text-sm" />
                                        <button variant="secondary">Save</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </x-card>
                @endforeach
            </div>
        @endif

        {{-- Inventory checklist --}}
        @if ($inventoryTasksByRoom->count())
            <x-card>
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold">Inventory</h3>
                </div>
                <ul class="divide-y dark:divide-gray-700">
                    @foreach ($inventoryTasksByRoom as $room)
                        @foreach ($room->tasks->where('type', 'inventory') as $task)
                            @php $item = $session->checklistItems->firstWhere('task_id',$task->id); @endphp
                            <li class="px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <form method="post" action="{{ route('checklist.toggle', [$session, $item]) }}">
                                        @csrf
                                        <button
                                            class="px-2 py-1 rounded border text-sm {{ $item?->checked ? 'bg-green-600 text-white border-green-600' : '' }}">
                                            {{ $item?->checked ? '✓' : 'Mark' }}
                                        </button>
                                    </form>
                                    <span>{{ $task->name }}</span>
                                </div>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </x-card>
        @endif

        {{-- Photos upload --}}
        @if ($stage === 'photos')
            <div class="space-y-6">
                @foreach ($rooms as $room)
                    <x-card>
                        <div class="px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold">{{ $room->name }}</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $photoCounts[$room->id] ?? 0 }}/8
                                photos</span>
                        </div>
                        <div class="p-4">
                            <form method="post" enctype="multipart/form-data"
                                action="{{ route('photos.store', [$session, $room->id]) }}"
                                class="flex items-center gap-2">
                                @csrf
                                <x-form.input type="file" name="photos[]" multiple accept="image/*"
                                    class="rounded border-gray-300" />
                                <x-button>Upload</x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach

                <x-card>
                    <form method="post" action="{{ route('sessions.complete', $session) }}">
                        @csrf
                        <x-button>Submit Checklist</x-button>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Requires ≥8 photos per room. Timestamp
                            overlay is
                            automatic on
                            upload.</p>
                    </form>
                </x-card>
            </div>
        @endif
    @endif
</x-app-layout>
