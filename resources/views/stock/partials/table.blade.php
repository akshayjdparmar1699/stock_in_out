<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b font-medium text-gray-700">{{ __('Stock Movement Ledger') }}</div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Qty (Base Unit)') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Qty (Alt Unit)') }}</th>
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
                    <td class="px-6 py-3 text-sm text-gray-700">
                        @if ($movement->item->hasAltUnit())
                            {{ number_format($movement->item->altUnitQuantity($movement->quantity), 2) }} {{ $movement->item->alt_unit }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->reason ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->user->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-sm text-right">
                        @if ($movement->referenceUrl())
                            <a href="{{ $movement->referenceUrl() }}" class="text-indigo-600 hover:underline">{{ __($movement->referenceLabel()) }}</a>
                        @elseif ($movement->type === 'in')
                            <form method="POST" action="{{ route('stock.destroy', $movement) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('Stock entry for '.$movement->item->name.' ('.$movement->quantity.' '.$movement->item->unit.')') }}; deleteOpen = true"
                                    class="text-red-500 hover:text-red-700 align-middle" title="{{ __('Delete') }}">
                                    <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No stock movements yet.') }}</td>
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
