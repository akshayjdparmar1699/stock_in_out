<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b font-medium text-gray-700">{{ __('Sold From This Batch') }}</div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Quantity') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($allocations as $allocation)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $allocation->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">{{ $allocation->quantity }} {{ $batch->item->unit }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $allocation->movement->user->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-right">
                        @if ($allocation->movement->referenceUrl())
                            <a href="{{ $allocation->movement->referenceUrl() }}" class="text-indigo-600 hover:underline">{{ __($allocation->movement->referenceLabel()) }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('This batch has not been sold from yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $allocations->links() }}
</div>
