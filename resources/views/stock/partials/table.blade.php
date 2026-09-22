<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b font-medium text-gray-700">{{ __('Stock Movement Ledger') }}</div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Quantity') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reason') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-3 text-sm text-gray-800">{{ $movement->item->name }}</td>
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ strtoupper($movement->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-700">{{ $movement->quantity }} {{ $movement->item->unit }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->reason ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->user->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-right">
                        @if ($movement->referenceUrl())
                            <a href="{{ $movement->referenceUrl() }}" class="text-indigo-600 hover:underline">{{ __($movement->referenceLabel()) }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No stock movements yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $movements->links() }}
</div>
