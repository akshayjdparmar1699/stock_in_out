<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Received') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Source') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Received Qty') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Remaining') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Remaining (Alt Unit)') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($batches as $batch)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.showCubeLoader(); window.location='{{ route('batches.show', $batch) }}'">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $batch->received_at->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-brand-700 hover:underline">{{ $batch->item->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if ($batch->sourceMovement?->referenceLabel() === 'View purchase')
                            {{ __('Supplier purchase') }}
                        @else
                            {{ __('Manual') }}
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $batch->quantity_in }} {{ $batch->item->unit }}</td>
                    <td class="px-6 py-4 text-sm {{ $batch->isDepleted() ? 'text-gray-400' : 'text-green-600 font-medium' }}">
                        {{ $batch->quantity_remaining }} {{ $batch->item->unit }}
                    </td>
                    <td class="px-6 py-4 text-sm {{ $batch->isDepleted() ? 'text-gray-400' : 'text-green-600 font-medium' }}">
                        @if ($batch->item->hasAltUnit())
                            {{ number_format($batch->item->altUnitQuantity($batch->quantity_remaining), 2) }} {{ $batch->item->alt_unit }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if ($batch->isDepleted())
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Sold out') }}</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('Active') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No stock batches yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($batches as $batch)
        <a href="{{ route('batches.show', $batch) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-brand-700 truncate">{{ $batch->item->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if ($batch->sourceMovement?->referenceLabel() === 'View purchase')
                        {{ __('Supplier purchase') }}
                    @else
                        {{ __('Manual') }}
                    @endif
                    &middot; {{ $batch->received_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-bold {{ $batch->isDepleted() ? 'text-gray-400' : 'text-green-600' }}">
                    {{ $batch->quantity_remaining }} {{ $batch->item->unit }}
                </p>
                <p class="text-xs text-gray-400">{{ __('of') }} {{ $batch->quantity_in }} {{ $batch->item->unit }}</p>
                <span @class([
                    'inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-medium',
                    'bg-gray-100 text-gray-600' => $batch->isDepleted(),
                    'bg-green-100 text-green-700' => ! $batch->isDepleted(),
                ])>{{ $batch->isDepleted() ? __('Sold out') : __('Active') }}</span>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No stock batches yet.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $batches->links() }}
</div>
