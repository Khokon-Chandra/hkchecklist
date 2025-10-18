<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">My Assignments</h2>
    </x-slot>

    <x-card class="mb-4">
        <table class="min-w-full text-sm">
            <thead class="uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Property</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
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
    </x-card>

    {{ $sessions->links() }}
</x-app-layout>
