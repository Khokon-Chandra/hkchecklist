<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg sm:text-xl font-semibold">Manage Assignment</h2>
    </x-slot>

    <x-card class="mb-4">
        <form method="get" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-end gap-3 px-1 sm:px-0">
            <div class="flex-1 min-w-0">
                <x-form.label value="Property" />
                <x-form.select name="property_id" class="!py-1 w-full max-w-full rounded border-gray-300">
                    <option value="">All</option>
                    @foreach ($properties as $p)
                        <option value="{{ $p->id }}" @selected($filters['property_id'] == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="flex-1 min-w-0">
                <x-form.label value="Housekeeper" />
                <x-form.select name="housekeeper_id" class="!py-1 w-full max-w-full rounded border-gray-300">
                    <option value="">All</option>
                    @foreach ($housekeepers as $hk)
                        <option value="{{ $hk->id }}" @selected($filters['housekeeper_id'] == $hk->id)>{{ $hk->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full sm:w-auto sm:shrink-0">
                <x-form.label value="Status" />
                <x-form.select name="status" class="!py-1 w-full sm:w-auto max-w-full rounded border-gray-300">
                    <option value="">All</option>
                    @foreach (['pending', 'in_progress', 'completed'] as $st)
                        <option value="{{ $st }}" @selected($filters['status'] === $st)>
                            {{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full sm:w-auto sm:shrink-0">
                <x-form.label value="From" />
                <x-form.input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full sm:w-auto max-w-full" />
            </div>
            <div class="w-full sm:w-auto sm:shrink-0">
                <x-form.label value="To" />
                <x-form.input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full sm:w-auto max-w-full" />
            </div>
            <div class="w-full sm:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <x-button class="w-full sm:w-auto whitespace-nowrap">Filter</x-button>
                <x-button href="{{ route('manage.sessions.create') }}" class="w-full sm:w-auto whitespace-nowrap">New Assignment</x-button>
            </div>
        </form>
    </x-card>

    <x-card class="!px-0">
        <table class="min-w-full text-sm">
            <thead class="uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Property</th>
                    <th class="px-4 py-2 text-left">Housekeeper</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 w-40">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($sessions as $s)
                    <tr>
                        <td class="px-4 py-2">{{ $s->scheduled_date->toDateString() }}</td>
                        <td class="px-4 py-2 truncate">{{ $s->property->name }}</td>
                        <td class="px-4 py-2">{{ $s->housekeeper?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-center"><x-status-badge :status="$s->status" /></td>
                        <td class="px-4 py-2 flex justify-end">
                            <a href="{{ route('sessions.show', $s) }}"
                                class="text-green-600 hover:underline">Open</a>
                            <span class="mx-2">·</span>

                            <a href="{{ route('manage.sessions.edit', $s) }}"
                                class="text-indigo-600 hover:underline">Edit</a>
                            <span class="mx-2">·</span>

                            <form method="post" action="{{ route('manage.sessions.destroy', $s) }}" class="inline"
                                onsubmit="return confirm('Delete assignment?')">
                                @csrf @method('delete')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="5">No assignments</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $sessions->links() }}</div>
</x-app-layout>
