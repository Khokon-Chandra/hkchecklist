<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl">Rooms — {{ $property->name }}</h2>
  </x-slot>

  <div class="flex items-center justify-between mb-4">
    <a href="{{ route('properties.index') }}" class="text-gray-600 hover:underline">← Back to Properties</a>
    <a href="{{ route('rooms.create',['property'=>$property->id]) }}" class="px-3 py-2 rounded bg-indigo-600 text-white">+ Add Room</a>
  </div>

  <div class="overflow-hidden rounded border bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr><th class="px-4 py-2 text-left">Name</th><th class="px-4 py-2">Default?</th><th class="px-4 py-2 w-40"></th></tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rooms as $r)
          <tr>
            <td class="px-4 py-2 font-medium">{{ $r->name }}</td>
            <td class="px-4 py-2 text-center">
              <span class="px-2 py-0.5 rounded text-xs {{ $r->is_default ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $r->is_default ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="px-4 py-2 text-right">
              <a class="text-indigo-600 hover:underline" href="{{ route('rooms.edit',[$property,$r]) }}">Edit</a>
              <span class="mx-2">·</span>
              <a class="text-blue-600 hover:underline" href="{{ route('tasks.index',['property'=>$property->id,'room'=>$r->id]) }}">Tasks</a>
            </td>
          </tr>
        @empty
          <tr><td class="px-4 py-6 text-center text-gray-500" colspan="3">No rooms yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-app-layout>
