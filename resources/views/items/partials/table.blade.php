<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('SKU') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Selling Price') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock (Base Unit)') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock (Alt Unit)') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                @php $qty = $item->stocks->first()->quantity ?? 0; @endphp
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.showCubeLoader(); window.location='{{ route('items.show', $item) }}'">
                    <td class="px-6 py-4 text-sm font-medium text-indigo-700 hover:underline">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->sku }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm {{ $qty <= $item->low_stock_threshold ? 'text-red-600 font-medium' : 'text-green-600 font-medium' }}">
                        {{ $qty }} {{ $item->unit }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if ($item->hasAltUnit())
                            {{ number_format($item->altUnitQuantity($qty), 2) }} {{ $item->alt_unit }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm" onclick="event.stopPropagation()">
                        <a href="{{ route('items.edit', $item) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('items.destroy', $item) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($item->name) }}; deleteOpen = true"
                                class="ml-3 text-red-500 hover:text-red-700 align-middle" title="{{ __('Delete') }}">
                                <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No items found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $items->links() }}
</div>
