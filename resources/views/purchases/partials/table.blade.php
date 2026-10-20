<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Purchase #') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supplier') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($purchases as $purchase)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.showCubeLoader(); window.location='{{ route('purchases.show', $purchase) }}'">
                    <td class="px-6 py-4 text-sm font-medium text-brand-700 hover:underline">{{ $purchase->purchase_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $purchase->purchase_date->format('d M Y') }}, {{ $purchase->created_at->format('h:i A') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $purchase->supplier->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">₹{{ number_format($purchase->total, 2) }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-700' => $purchase->status === 'paid',
                            'bg-yellow-100 text-yellow-700' => $purchase->status === 'partial',
                            'bg-red-100 text-red-700' => $purchase->status === 'unpaid',
                        ])>{{ ucfirst($purchase->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm" @click.stop>
                        <a href="{{ route('purchases.edit', $purchase) }}" onclick="window.showCubeLoader()" class="text-brand-600 hover:underline mr-3">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('Purchase '.$purchase->purchase_number) }}; deleteOpen = true"
                                class="text-red-500 hover:text-red-700" title="{{ __('Delete') }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No purchases found for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($purchases as $purchase)
        <div onclick="window.showCubeLoader(); window.location='{{ route('purchases.show', $purchase) }}'"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 cursor-pointer">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-brand-700 truncate">{{ $purchase->purchase_number }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $purchase->supplier->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $purchase->purchase_date->format('d M Y') }}, {{ $purchase->created_at->format('h:i A') }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-gray-900">&#8377;{{ number_format($purchase->total, 2) }}</p>
                    <span @class([
                        'inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium',
                        'bg-green-100 text-green-700' => $purchase->status === 'paid',
                        'bg-yellow-100 text-yellow-700' => $purchase->status === 'partial',
                        'bg-red-100 text-red-700' => $purchase->status === 'unpaid',
                    ])>{{ ucfirst($purchase->status) }}</span>
                </div>
            </div>
            <div class="flex items-center justify-end gap-1.5 mt-2 pt-2 border-t border-gray-50" @click.stop>
                <a href="{{ route('purchases.edit', $purchase) }}" onclick="window.showCubeLoader()" title="{{ __('Edit') }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                </a>
                <form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                        @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('Purchase '.$purchase->purchase_number) }}; deleteOpen = true"
                        class="w-8 h-8 rounded-lg text-red-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600" title="{{ __('Delete') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No purchases found for this filter.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $purchases->links() }}
</div>
