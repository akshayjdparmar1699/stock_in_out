{{-- Desktop table --}}
<div class="hidden lg:block">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Quantity') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock After') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reason') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                <tr>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-3 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ strtoupper($movement->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm font-medium {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $movement->type === 'in' ? '+' : '-' }}{{ rtrim(rtrim(number_format($movement->quantity, 2), '0'), '.') }} {{ $item->unit }}
                    </td>
                    <td class="px-6 py-3 text-sm font-medium text-gray-800">
                        @php $remaining = $remainingByMovement[$movement->id] ?? null; @endphp
                        @if ($remaining !== null)
                            {{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} {{ $item->unit }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">
                        {{ $movement->reason ?? '—' }}
                        @if ($movement->referenceUrl())
                            <a href="{{ $movement->referenceUrl() }}" class="text-brand-600 hover:underline ml-1">{{ __($movement->referenceLabel()) }}</a>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $movement->user->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No stock history for this item yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile list --}}
<div class="lg:hidden divide-y divide-gray-100">
    @forelse ($movements as $movement)
        @php $remaining = $remainingByMovement[$movement->id] ?? null; @endphp
        <div class="flex items-center gap-3 px-4 py-3">
            <span @class([
                'w-9 h-9 rounded-lg flex items-center justify-center shrink-0',
                'bg-green-50 text-green-600' => $movement->type === 'in',
                'bg-red-50 text-red-600' => $movement->type !== 'in',
            ])>
                @if ($movement->type === 'in')
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-6 6m6-6l6 6" /></svg>
                @else
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l6-6m-6 6l-6-6" /></svg>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $movement->reason ?? ($movement->referenceLabel() ? __($movement->referenceLabel()) : ($movement->type === 'in' ? __('Stock added') : __('Stock removed'))) }}</p>
                <p class="text-xs text-gray-400">
                    {{ $movement->created_at->format('d M, h:i A') }}
                    @if ($remaining !== null)
                        &middot; {{ __('Left') }} {{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }} {{ $item->unit }}
                    @endif
                </p>
            </div>
            <span class="text-sm font-semibold shrink-0 {{ $movement->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                {{ $movement->type === 'in' ? '+' : '-' }}{{ rtrim(rtrim(number_format($movement->quantity, 2), '0'), '.') }} {{ $item->unit }}
            </span>
        </div>
    @empty
        <div class="px-6 py-10 text-sm text-gray-400 text-center">{{ __('No stock history for this item yet.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4 px-4 lg:px-0 pb-4 lg:pb-0" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $movements->links() }}
</div>
