@php
    $relativeLabel = function ($date) {
        $days = $date->copy()->startOfDay()->diffInDays(now()->startOfDay());
        if ($days === 0) return __('today');
        if ($days === 1) return __('1 day ago');
        return __(':days days ago', ['days' => $days]);
    };
    $statusBadge = [
        'full' => ['bg-green-100 text-green-700', __('Full')],
        'selling' => ['bg-amber-100 text-amber-700', __('Selling')],
        'sold_out' => ['bg-gray-100 text-gray-500', __('Sold out')],
    ];
@endphp

{{-- Desktop table --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Came in') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('From') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sold so far') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Left in stock') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($batches as $batch)
                @php
                    $sold = $batch->quantitySold();
                    $pct = (float) $batch->quantity_in > 0 ? min(100, round($sold / (float) $batch->quantity_in * 100)) : 0;
                    $isPurchase = $batch->sourceMovement?->referenceLabel() === 'View purchase';
                    [$badgeClass, $badgeLabel] = $statusBadge[$batch->statusKey()];
                @endphp
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.showCubeLoader(); window.location='{{ route('batches.show', $batch) }}'">
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ $batch->received_at->format('d M') }}
                        <span class="block text-xs text-gray-400">{{ $batch->received_at->format('h:i A') }} &middot; {{ $relativeLabel($batch->received_at) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-brand-700 hover:underline">{{ $batch->item->name }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if ($isPurchase)
                            <span class="px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">{{ __('Supplier purchase') }}</span>
                            <span class="block text-xs text-gray-400 mt-0.5">
                                <a href="{{ $batch->sourceMovement->referenceUrl() }}" class="text-brand-600 hover:underline" @click.stop>{{ $batch->sourceMovement->reference?->purchase_number }}</a>
                            </span>
                        @else
                            <span class="px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">{{ __('Manual add') }}</span>
                            <span class="block text-xs text-gray-400 mt-0.5">{{ $batch->sourceMovement?->reason ?? '—' }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        {{ rtrim(rtrim(number_format($sold, 2), '0'), '.') }} {{ __('of') }} {{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}
                        <div class="w-32 h-1.5 rounded-full bg-gray-100 overflow-hidden mt-1.5">
                            <div class="h-full rounded-full {{ $batch->statusKey() === 'sold_out' ? 'bg-gray-400' : 'bg-brand-600' }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $pct }}%</span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="font-semibold {{ $batch->statusKey() === 'sold_out' ? 'text-gray-400' : 'text-green-600' }}">
                            {{ rtrim(rtrim(number_format($batch->quantity_remaining, 2), '0'), '.') }} {{ $batch->item->unit }}
                        </span>
                        @if ($batch->item->hasAltUnit())
                            <span class="block text-xs text-gray-400">= {{ number_format($batch->item->altUnitQuantity($batch->quantity_remaining), 2) }} {{ $batch->item->alt_unit }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No lots yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($batches as $batch)
        @php
            $sold = $batch->quantitySold();
            $pct = (float) $batch->quantity_in > 0 ? min(100, round($sold / (float) $batch->quantity_in * 100)) : 0;
            $isPurchase = $batch->sourceMovement?->referenceLabel() === 'View purchase';
            [$badgeClass, $badgeLabel] = $statusBadge[$batch->statusKey()];
        @endphp
        <a href="{{ route('batches.show', $batch) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 block">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $batch->item->name }}</p>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0 {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">
                @if ($isPurchase)
                    {{ $batch->sourceMovement->reference?->purchase_number }}
                @else
                    {{ __('Manual') }}
                @endif
                &middot; {{ $batch->received_at->format('d M, h:i A') }}
            </p>
            <div class="w-full h-1.5 rounded-full bg-gray-100 overflow-hidden mt-2.5">
                <div class="h-full rounded-full {{ $batch->statusKey() === 'sold_out' ? 'bg-gray-400' : 'bg-brand-600' }}" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-xs text-gray-500">{{ __('Sold') }} {{ rtrim(rtrim(number_format($sold, 2), '0'), '.') }} {{ __('of') }} {{ rtrim(rtrim(number_format($batch->quantity_in, 2), '0'), '.') }} {{ $batch->item->unit }}</span>
                <span class="text-xs font-semibold {{ $batch->statusKey() === 'sold_out' ? 'text-gray-400' : 'text-green-600' }}">{{ __('Left') }} {{ rtrim(rtrim(number_format($batch->quantity_remaining, 2), '0'), '.') }} {{ $batch->item->unit }}</span>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No lots yet.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $batches->links() }}
</div>
