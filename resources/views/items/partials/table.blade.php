@php
    $iconPalette = [
        'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700', 'bg-green-100 text-green-700',
        'bg-amber-100 text-amber-700', 'bg-pink-100 text-pink-700', 'bg-teal-100 text-teal-700',
        'bg-indigo-100 text-indigo-700', 'bg-orange-100 text-orange-700',
    ];
@endphp

{{-- Table view (always on desktop; mobile when toggled to table view) --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Selling Price') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Loose Stock') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                @php
                    $qty = $item->stocks->first()->quantity ?? 0;
                    $isOut = $qty <= 0;
                    $isLow = ! $isOut && $qty <= $item->low_stock_threshold;
                    $icon = $iconPalette[$item->id % count($iconPalette)];
                @endphp
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('items.show', $item) }}" class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center text-[10px] font-bold uppercase shrink-0 {{ $icon }}">{{ Str::limit($item->unit, 3, '') }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-brand-700 hover:underline truncate">{{ $item->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $item->sku ?: '—' }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        &#8377;{{ number_format($item->selling_price, 2) }}
                        <span class="text-gray-400">/ {{ $item->unit }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-green-600') }}">
                                {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }} {{ $item->unit }}
                            </span>
                            <span @class([
                                'px-2 py-0.5 rounded-full text-[11px] font-medium',
                                'bg-red-50 text-red-600' => $isOut,
                                'bg-amber-50 text-amber-600' => $isLow,
                                'bg-green-50 text-green-700' => ! $isOut && ! $isLow,
                            ])>
                                {{ $isOut ? __('Out') : ($isLow ? __('Low') : __('In stock')) }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ __('Alert at') }} {{ $item->low_stock_threshold }} {{ $item->unit }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if ($item->hasAltUnit())
                            {{ number_format($item->altUnitQuantity($qty), 2) }} {{ $item->alt_unit }}
                        @else
                            {{ __('Not sold loose') }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                            <a href="{{ route('items.edit', $item) }}" title="{{ __('Edit') }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                            </a>
                            <div class="relative">
                                <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zm6 2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" style="display: none"
                                    x-transition class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
                                    <form method="POST" action="{{ route('items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($item->name) }}; deleteOpen = true; menuOpen = false"
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
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No items found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($items as $item)
        @php
            $qty = $item->stocks->first()->quantity ?? 0;
            $isOut = $qty <= 0;
            $isLow = ! $isOut && $qty <= $item->low_stock_threshold;
            $icon = $iconPalette[$item->id % count($iconPalette)];
        @endphp
        <a href="{{ route('items.show', $item) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-lg flex items-center justify-center text-[10px] font-bold uppercase shrink-0 {{ $icon }}">{{ Str::limit($item->unit, 3, '') }}</span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-gray-900 truncate">{{ $item->name }}</span>
                <span class="block text-xs text-gray-400">&#8377;{{ number_format($item->selling_price, 2) }} / {{ $item->unit }}</span>
            </span>
            <span class="text-right shrink-0">
                <span class="block text-sm font-bold {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-green-600') }}">
                    {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }} {{ $item->unit }}
                </span>
                <span @class([
                    'inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium',
                    'bg-red-50 text-red-600' => $isOut,
                    'bg-amber-50 text-amber-600' => $isLow,
                    'bg-green-50 text-green-700' => ! $isOut && ! $isLow,
                ])>
                    {{ $isOut ? __('Out') : ($isLow ? __('Low') : __('In stock')) }}
                </span>
            </span>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No items found.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $items->links() }}
</div>
