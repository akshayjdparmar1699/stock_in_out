<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Purchase') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supplier') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($purchases as $purchase)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.showCubeLoader(); window.location='{{ route('purchases.show', $purchase) }}'">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-brand-700 hover:underline">{{ $purchase->purchase_number }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $purchase->purchase_date->format('d M') }}, {{ $purchase->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $purchase->supplier->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">&#8377;{{ number_format($purchase->total, 2) }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $purchase->status === 'paid',
                            'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                            'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                        ])>
                            {{ ucfirst($purchase->status) }}@if ($purchase->status === 'partial') &middot; &#8377;{{ number_format($purchase->balanceDue(), 0) }} {{ __('due') }}@endif
                        </span>
                    </td>
                    <td class="px-6 py-4" @click.stop>
                        <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                            <button type="button" title="{{ __('Share on WhatsApp') }}"
                                onclick="window.open('https://wa.me/?text=' + encodeURIComponent({{ \Illuminate\Support\Js::from($purchase->shareMessage()) }}), '_blank')"
                                class="w-8 h-8 rounded-lg border border-green-200 text-green-600 flex items-center justify-center hover:bg-green-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                            </button>
                            <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" title="{{ __('Print') }}"
                                class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25C7.629 2.25 7.125 2.754 7.125 3.375v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                            </a>
                            <div class="relative">
                                <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zm6 2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" style="display: none"
                                    x-transition class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
                                    <a href="{{ route('purchases.edit', $purchase) }}" onclick="window.showCubeLoader()" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from('Purchase '.$purchase->purchase_number) }}; deleteOpen = true; menuOpen = false"
                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No purchases found for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($purchases as $purchase)
        <a href="{{ route('purchases.show', $purchase) }}" onclick="window.showCubeLoader()" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ $purchase->supplier->name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $purchase->purchase_number }} &middot; {{ $purchase->created_at->format('h:i A') }}</div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right">
                    <div class="text-sm font-bold text-gray-900">&#8377;{{ number_format($purchase->total, 2) }}</div>
                    <span @class([
                        'inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium',
                        'bg-green-50 text-green-700' => $purchase->status === 'paid',
                        'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                        'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                    ])>{{ ucfirst($purchase->status) }}</span>
                </div>
                <button type="button" title="{{ __('Share on WhatsApp') }}" @click.prevent.stop="window.open('https://wa.me/?text=' + encodeURIComponent({{ \Illuminate\Support\Js::from($purchase->shareMessage()) }}), '_blank')"
                    class="w-9 h-9 rounded-full border border-green-200 text-green-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                </button>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No purchases found for this filter.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $purchases->links() }}
</div>
