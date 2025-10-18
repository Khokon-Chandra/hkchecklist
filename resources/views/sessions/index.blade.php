<x-app-layout>
    <h1 class="text-xl font-semibold mb-4">My Assignments</h1>
    <table class="w-full border">
        <tr class="bg-gray-100">
            <th>Date</th>
            <th>Property</th>
            <th>Status</th>
            <th></th>
        </tr>
        @foreach ($sessions as $s)
            <tr class="border-b">
                <td>{{ $s->scheduled_date->toFormattedDateString() }}</td>
                <td>{{ $s->property->name }}</td>
                <td>{{ ucfirst($s->status) }}</td>
                <td><a class="text-blue-600" href="{{ route('sessions.show', $s) }}">Open</a></td>
            </tr>
        @endforeach
    </table>
    {{ $sessions->links() }}
</x-app-layout><x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">My Assignments</h2>
    </x-slot>

    <div class="overflow-hidden rounded border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Property</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($sessions as $s)
                    <tr>
                        <td class="px-4 py-2">{{ $s->scheduled_date->toFormattedDateString() }}</td>
                        <td class="px-4 py-2">{{ $s->property->name }}</td>
                        <td class="px-4 py-2 text-center"><x-status-badge :status="$s->status" /></td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('sessions.show', $s) }}" class="text-indigo-600 hover:underline">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="4">No sessions</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sessions->links() }}
</x-app-layout>
