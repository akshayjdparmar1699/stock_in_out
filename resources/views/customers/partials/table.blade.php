@php
    $avatarPalette = [
        'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700', 'bg-pink-100 text-pink-700', 'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700',
    ];
    $initials = fn (string $name) => collect(explode(' ', trim($name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

{{-- Table view (always shown on desktop; shown on mobile only when toggled) --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Customer') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Village / City') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Balance Due') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Last Bill') }}</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($customers as $customer)
                @php
                    $due = $dueByCustomerId[$customer->id] ?? 0;
                    $limit = (float) $customer->credit_limit;
                    $isOver = $limit > 0 && $due > $limit;
                    $limitRatio = $limit > 0 ? ($due / $limit) : 0;
                    $limitPct = $limit > 0 ? min(round($limitRatio * 100), 100) : 0;
                    $barColor = $isOver ? 'bg-red-500' : ($limitRatio >= 0.8 ? 'bg-amber-400' : 'bg-brand-400');
                    $lastBill = $lastBillByCustomerId[$customer->id] ?? null;
                    $avatar = $avatarPalette[$customer->id % count($avatarPalette)];
                @endphp
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('customers.show', $customer) }}" class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 {{ $avatar }}">{{ $initials($customer->name) }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-brand-700 hover:underline truncate">{{ $customer->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $customer->phone }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $customer->address ?: '—' }}</td>
                    <td class="px-6 py-4 min-w-[160px]">
                        @if ($due > 0)
                            <div class="text-sm font-semibold text-red-600">&#8377;{{ number_format($due, 0) }}</div>
                            @if ($limit > 0)
                                <div class="text-xs text-gray-400">{{ __('of') }} &#8377;{{ number_format($limit, 0) }}</div>
                                <div class="relative h-1.5 rounded-full bg-gray-100 overflow-hidden mt-1.5 w-32">
                                    <div class="absolute inset-y-0 left-0 {{ $barColor }}" style="width: {{ $limitPct }}%"></div>
                                </div>
                                @if ($isOver)
                                    <div class="text-xs text-red-500 font-medium mt-1">{{ __('Over limit by') }} &#8377;{{ number_format($due - $limit, 0) }}</div>
                                @endif
                            @endif
                        @elseif ($due < 0)
                            <div class="text-sm font-semibold text-green-600">&#8377;{{ number_format(abs($due), 0) }} {{ __('CR') }}</div>
                        @else
                            <div class="text-sm font-medium text-green-600">{{ __('Settled') }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lastBill ? $lastBill->format('d M') : '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="{{ route('customers.toggle-active', $customer) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                data-name="{{ $customer->name }}"
                                data-active="{{ $customer->is_active ? '1' : '0' }}"
                                @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                title="{{ $customer->is_active ? __('Click to mark inactive') : __('Click to mark active') }}"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $customer->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('customers.edit', $customer) }}" title="{{ __('Edit') }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                            </a>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($customer->name) }}; deleteOpen = true"
                                    title="{{ __('Delete') }}" class="w-8 h-8 rounded-lg text-red-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No customers found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @php
        $borderPalette = [
            'border-l-orange-400', 'border-l-purple-400', 'border-l-blue-400',
            'border-l-green-400', 'border-l-pink-400', 'border-l-amber-400',
            'border-l-indigo-400', 'border-l-teal-400',
        ];
    @endphp
    @forelse ($customers as $customer)
        @php
            $due = $dueByCustomerId[$customer->id] ?? 0;
            $limit = (float) $customer->credit_limit;
            $isOver = $limit > 0 && $due > $limit;
            $limitRatio = $limit > 0 ? ($due / $limit) : 0;
            $limitPct = $limit > 0 ? min(round($limitRatio * 100), 100) : 0;
            $barColor = $isOver ? 'bg-red-500' : ($limitRatio >= 0.8 ? 'bg-amber-400' : 'bg-brand-400');
            $lastBill = $lastBillByCustomerId[$customer->id] ?? null;
            $cardBorder = $borderPalette[$customer->id % count($borderPalette)];
        @endphp
        <div onclick="window.location.href='{{ route('customers.show', $customer) }}'"
            class="bg-white rounded-xl border-y border-r border-gray-100 border-l-4 {{ $cardBorder }} shadow-sm p-4 cursor-pointer">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $customer->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $customer->address ?: '—' }} &middot; {{ __('Last bill') }} {{ $lastBill ? $lastBill->format('d M') : '—' }}</p>
                </div>
                <div class="text-right shrink-0">
                    @if ($due > 0)
                        <p class="text-base font-bold text-red-600">&#8377;{{ number_format($due, 0) }}</p>
                        @if ($limit > 0)
                            <p class="text-xs text-gray-400">{{ __('limit') }} &#8377;{{ number_format($limit, 0) }}</p>
                        @endif
                    @elseif ($due < 0)
                        <p class="text-base font-bold text-green-600">&#8377;{{ number_format(abs($due), 0) }} {{ __('CR') }}</p>
                    @else
                        <p class="text-sm font-semibold text-green-600">{{ __('Settled') }}</p>
                    @endif
                </div>
            </div>

            @if ($due > 0 && $limit > 0)
                <div class="relative h-1.5 rounded-full bg-gray-100 overflow-hidden mt-2.5">
                    <div class="absolute inset-y-0 left-0 {{ $barColor }}" style="width: {{ $limitPct }}%"></div>
                </div>
                @if ($isOver)
                    <p class="text-xs text-red-500 font-medium mt-1">{{ __('Over limit by') }} &#8377;{{ number_format($due - $limit, 0) }}</p>
                @endif
            @endif

            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50">
                @if (! $isOver)
                    <span class="text-xs text-gray-400">{{ $customer->phone }}</span>
                @else
                    <span></span>
                @endif
                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <a href="{{ route('customers.edit', $customer) }}" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    </a>
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                            @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($customer->name) }}; deleteOpen = true"
                            class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No customers found.') }}</div>
    @endforelse
</div>

{{-- mb-20 on mobile clears the "+" FAB (fixed, bottom-20, 56px tall) so its Next link isn't covered. --}}
<div class="flex flex-wrap items-center justify-between gap-3 mt-4 mb-20 lg:mb-0" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $customers->links() }}
</div>
