<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('SKU') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Selling Price') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock (this branch)') }}</th>
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
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('items.edit', $item) }}" onclick="event.stopPropagation()" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No items found.') }}</td>
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
