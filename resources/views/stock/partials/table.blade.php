@php
    $lastDate = null;
    $groupLabel = function ($date) {
        if ($date->isToday()) return __('Today').', '.$date->format('d M');
        if ($date->isYesterday()) return __('Yesterday').', '.$date->format('d M');
        return $date->format('d M Y');
    };
@endphp

{{-- Desktop table --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Time') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Quantity') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock After') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Source') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                @php
                    $date = $movement->created_at;
                    $isNewGroup = $lastDate === null || ! $date->isSameDay($lastDate);
                    $lastDate = $date;
                    $remaining = $remainingByMovement[$movement->id] ?? null;
                    $isPurchase = $movement->reference_type === \App\Models\Purchase::class;
                    $isSale = $movement->reference_type === \App\Models\Invoice::class;
                    $isManual = ! $isPurchase && ! $isSale;
                @endphp
                @if ($isNewGroup)
                    <tr>
                        <td colspan="6" class="px-6 py-2 text-xs font-semibold text-brand-700 bg-brand-50/60">{{ $groupLabel($date) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $date->format('g:i A') }}</td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2.5">
                            <span @class([
                                'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                                'bg-green-100 text-green-700' => $movement->type === 'in',
                                'bg-red-100 text-red-600' => $movement->type === 'out',
                            ])>
                                @if ($movement->type === 'in')
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" /></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l6.75 6.75M12 4.5l-6.75 6.75" /></svg>
                                @endif
                            </span>
                            <span class="text-sm font-medium text-gray-800">{{ $movement->item->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm">
                        <span class="font-bold {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $movement->type === 'in' ? '+' : '-' }}{{ rtrim(rtrim(number_format($movement->quantity, 2), '0'), '.') }} {{ $movement->item->unit }}
                        </span>
                        @if ($movement->item->hasAltUnit())
                            <div class="text-xs text-gray-400">= {{ number_format($movement->item->altUnitQuantity($movement->quantity), 2) }} {{ $movement->item->alt_unit }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm font-semibold text-gray-900">
                        @if ($remaining !== null)
                            {{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} {{ $movement->item->unit }}
                            @if ($movement->item->hasAltUnit())
                                <div class="text-xs text-gray-400 font-normal">= {{ number_format($movement->item->altUnitQuantity($remaining), 2) }} {{ $movement->item->alt_unit }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm">
                        <div class="flex items-center gap-2">
                            @if ($isPurchase)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 shrink-0">{{ __('Purchase') }}</span>
                                <a href="{{ $movement->referenceUrl() }}" class="text-brand-600 hover:underline font-medium">{{ $movement->reference->purchase_number ?? __('View') }}</a>
                            @elseif ($isSale)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700 shrink-0">{{ __('Sale') }}</span>
                                <a href="{{ $movement->referenceUrl() }}" class="text-brand-600 hover:underline font-medium">{{ $movement->reference->invoice_number ?? __('View') }}</a>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 shrink-0">{{ __('Manual') }}</span>
                                <span class="text-gray-500 truncate max-w-[12rem]" title="{{ $movement->reason }}">{{ $movement->reason ?? '—' }}</span>
                                @if ($movement->type === 'in')
                                    <a href="{{ route('stock.edit', $movement) }}" title="{{ __('Edit') }}" class="w-7 h-7 rounded-md border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-gray-50 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->user->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No stock movements yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

@php $lastDate = null; @endphp

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($movements as $movement)
        @php
            $date = $movement->created_at;
            $isNewGroup = $lastDate === null || ! $date->isSameDay($lastDate);
            $lastDate = $date;
            $remaining = $remainingByMovement[$movement->id] ?? null;
            $isPurchase = $movement->reference_type === \App\Models\Purchase::class;
            $isSale = $movement->reference_type === \App\Models\Invoice::class;
            $isManual = ! $isPurchase && ! $isSale;
        @endphp
        @if ($isNewGroup)
            <div class="pt-1 text-xs font-semibold text-brand-700">{{ $groupLabel($date) }}</div>
        @endif
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <span @class([
                'w-10 h-10 rounded-full flex items-center justify-center shrink-0',
                'bg-green-100 text-green-700' => $movement->type === 'in',
                'bg-red-100 text-red-600' => $movement->type === 'out',
            ])>
                @if ($movement->type === 'in')
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75" /></svg>
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l6.75 6.75M12 4.5l-6.75 6.75" /></svg>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ $movement->item->name }}</div>
                <div class="flex items-center gap-1.5 flex-wrap mt-0.5 text-xs text-gray-400">
                    @if ($isPurchase)
                        <span class="px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">{{ __('Purchase') }}</span>
                        <span>{{ $movement->reference->purchase_number ?? '' }}</span>
                    @elseif ($isSale)
                        <span class="px-1.5 py-0.5 rounded-full bg-violet-50 text-violet-700 font-medium">{{ __('Sale') }}</span>
                        <span>{{ $movement->reference->invoice_number ?? '' }}</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">{{ __('Manual') }}</span>
                        <span class="truncate">{{ $movement->reason ?? '' }}</span>
                    @endif
                    <span>&middot; {{ $date->format('g:i A') }}</span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-sm font-bold {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $movement->type === 'in' ? '+' : '-' }}{{ rtrim(rtrim(number_format($movement->quantity, 2), '0'), '.') }} {{ $movement->item->unit }}
                </div>
                @if ($remaining !== null)
                    <div class="text-xs text-gray-400 mt-0.5">{{ __('Left') }} {{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} {{ $movement->item->unit }}</div>
                @endif
            </div>
            @if ($isManual && $movement->type === 'in')
                <a href="{{ route('stock.edit', $movement) }}" class="w-8 h-8 rounded-md border border-gray-200 text-gray-500 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                </a>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No stock movements yet.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $movements->links() }}
</div>
