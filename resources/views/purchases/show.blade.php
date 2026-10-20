@php
    $balanceDue = $purchase->balanceDue();
    $paymentPct = $purchase->total > 0 ? min(100, round(($purchase->paid_amount / $purchase->total) * 100)) : 100;
    $stockAdded = $purchase->items->groupBy(fn ($line) => $line->item->unit)
        ->map(fn ($lines) => rtrim(rtrim(number_format($lines->sum('quantity'), 2), '0'), '.').' '.$lines->first()->item->unit)
        ->values()
        ->implode(', ');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" onclick="window.showCubeLoader()" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-2 text-sm text-gray-400">
                    <a href="{{ route('purchases.index') }}" class="hover:text-gray-600">{{ __('Purchases') }}</a>
                    <span>/</span>
                    <span class="text-gray-800 font-semibold">{{ $purchase->purchase_number }}</span>
                    <span @class([
                        'px-2 py-0.5 rounded-full text-xs font-medium uppercase',
                        'bg-green-50 text-green-700' => $purchase->status === 'paid',
                        'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                        'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                    ])>{{ $purchase->status === 'partial' ? __('Partly paid') : $purchase->status }}</span>
                </nav>
                <h2 class="font-semibold text-lg text-gray-800 leading-tight truncate lg:hidden">{{ $purchase->purchase_number }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 print:hidden shrink-0">
                <a href="{{ route('purchases.edit', $purchase) }}" onclick="window.showCubeLoader()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('purchases.create') }}" onclick="window.showCubeLoader()"
                    class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">
                    {{ __('+ New Purchase') }}
                </a>
            </div>
            <a href="{{ route('purchases.edit', $purchase) }}" class="lg:hidden shrink-0 text-gray-500 p-1 print:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            </a>
        </div>
    </x-slot>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.prefetchPdf?.(
                {{ \Illuminate\Support\Js::from(route('purchases.pdf', $purchase)) }},
                {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }}
            );
        });
    </script>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{ payOpen: false, mode: 'cash', amount: null }"
        x-init="
            const url = {{ Illuminate\Support\Js::from(session('open_whatsapp_url')) }};
            if (url) { window.open(url, '_blank'); }
        ">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                {{-- Purchase card --}}
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-100 lg:border-gray-200 p-5 lg:p-8">

                    {{-- Mobile summary --}}
                    <div class="lg:hidden text-center pb-5 border-b border-gray-100 mb-5">
                        <span @class([
                            'inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase mb-3',
                            'bg-green-50 text-green-700' => $purchase->status === 'paid',
                            'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                            'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                        ])>
                            {{ $purchase->status === 'partial' ? __('Partly paid') : $purchase->status }}
                        </span>
                        <div class="text-3xl font-bold text-gray-900">&#8377;{{ number_format($purchase->total, 2) }}</div>
                        <div class="mt-2 text-sm font-medium text-gray-800">{{ $purchase->supplier->name }}</div>
                        <div class="text-xs text-gray-400">
                            {{ $purchase->supplier->phone }} &middot; {{ $purchase->purchase_date->format('d M Y') }}, {{ $purchase->created_at->format('h:i A') }}
                        </div>
                    </div>

                    <div class="hidden lg:flex justify-between mb-6">
                        <div>
                            <div class="text-xs font-semibold tracking-wide text-brand-600 uppercase mb-1">{{ __('Purchase from') }}</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $purchase->supplier->name }}</div>
                            <div class="text-sm text-gray-500">{{ $purchase->supplier->phone }}</div>
                            <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="text-sm font-medium text-brand-600 hover:underline">{{ __('Open supplier khata') }} &rsaquo;</a>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ $purchase->purchase_number }}</div>
                            <div class="text-sm text-gray-400 mt-1">{{ $purchase->purchase_date->format('d M Y') }}, {{ $purchase->created_at->format('h:i A') }}</div>
                            <div class="text-sm text-gray-400 mt-1">{{ __('Received at') }} {{ $purchase->branch->name }}</div>
                            <div class="text-sm text-gray-400">{{ __('Recorded by') }} {{ $purchase->user->name ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="mb-6">
                        <p class="lg:hidden text-xs font-semibold tracking-wide text-gray-400 uppercase mb-2">{{ __('Items received') }}</p>
                        <table class="w-full hidden lg:table">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="py-2 text-left text-xs font-medium text-gray-400 uppercase">{{ __('Item received') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Qty') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Cost per unit') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($purchase->items as $line)
                                    <tr>
                                        <td class="py-2.5 text-sm text-gray-800">{{ $line->item->name }}</td>
                                        <td class="py-2.5 text-sm text-gray-600 text-right">
                                            {{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }} {{ $line->item->unit }}
                                            @if ($line->item->alt_unit && $line->item->alt_unit_ratio)
                                                <div class="text-xs text-gray-400">= {{ rtrim(rtrim(number_format($line->quantity * $line->item->alt_unit_ratio, 2), '0'), '.') }} {{ $line->item->alt_unit }}</div>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-sm text-gray-600 text-right">&#8377;{{ number_format($line->unit_cost, 2) }}</td>
                                        <td class="py-2.5 text-sm text-gray-900 text-right font-medium">&#8377;{{ number_format($line->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="lg:hidden divide-y divide-gray-50 border-t border-gray-50">
                            @foreach ($purchase->items as $line)
                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm text-gray-800 truncate">{{ $line->item->name }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }} {{ $line->item->unit }} &times; &#8377;{{ number_format($line->unit_cost, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900 shrink-0">&#8377;{{ number_format($line->total, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($stockAdded)
                        <div class="bg-green-50 border border-green-200 rounded-md px-3 py-2 text-sm text-green-700 flex items-center gap-1.5 mb-6">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $stockAdded }} {{ __('added to stock') }}
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <div class="w-full sm:w-72 space-y-1">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Subtotal') }}</span>
                                <span>&#8377;{{ number_format($purchase->subtotal, 2) }}</span>
                            </div>
                            @if ($purchase->discount > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Discount') }}</span>
                                    <span>- &#8377;{{ number_format($purchase->discount, 2) }}</span>
                                </div>
                            @endif
                            @if ($purchase->tax > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Tax') }}</span>
                                    <span>+ &#8377;{{ number_format($purchase->tax, 2) }}</span>
                                </div>
                            @endif
                            @if ($purchase->transportation > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Transport') }}</span>
                                    <span>+ &#8377;{{ number_format($purchase->transportation, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-100 pt-1.5 mt-1.5">
                                <span>{{ __('Total') }}</span>
                                <span>&#8377;{{ number_format($purchase->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Paid so far') }}</span>
                                <span>&#8377;{{ number_format($purchase->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold rounded-md px-3 py-2 mt-1 {{ $balanceDue > 0 ? 'text-red-700 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                <span>{{ $balanceDue > 0 ? __('We still owe on this') : __('Settled') }}</span>
                                <span>&#8377;{{ number_format(abs($balanceDue), 2) }}{{ $balanceDue < 0 ? ' CR' : '' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($purchase->notes)
                        <div class="mt-6 text-sm text-gray-500">
                            <div class="font-medium text-gray-700">{{ __('Notes') }}</div>
                            {{ $purchase->notes }}
                        </div>
                    @endif
                </div>

                {{-- Mobile payment card --}}
                <div class="lg:hidden bg-white shadow-sm rounded-lg border border-gray-100 p-4 mt-4 print:hidden">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800">{{ __('Paid to supplier') }}</h3>
                        <span @class([
                            'px-2.5 py-1 rounded-full text-xs font-semibold',
                            'bg-green-50 text-green-700' => $purchase->status === 'paid',
                            'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                            'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                        ])>
                            {{ $purchase->status === 'paid' ? __('Fully paid') : ($purchase->status === 'partial' ? $paymentPct.'% '.__('paid') : __('Unpaid')) }}
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-500 mb-1">
                        <span>{{ __('Paid') }}</span>
                        <span class="font-medium text-gray-800">&#8377;{{ number_format($purchase->paid_amount, 2) }} {{ __('of') }} &#8377;{{ number_format($purchase->total, 2) }}</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full {{ $purchase->status === 'paid' ? 'bg-green-500' : 'bg-brand-500' }}" style="width: {{ $paymentPct }}%"></div>
                    </div>

                    @if ($purchase->payments->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2.5">
                            @foreach ($purchase->payments as $payment)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <div class="text-gray-800">{{ $payment->note ?: __('Payment') }}{{ $payment->mode ? ' · '.ucfirst($payment->mode) : '' }}</div>
                                        <div class="text-xs text-gray-400">{{ $payment->created_at->format('d M, h:i A') }} &middot; {{ $payment->user->name ?? '—' }}</div>
                                    </div>
                                    <span class="font-medium text-gray-800 shrink-0">&#8377;{{ number_format($payment->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        @if ($balanceDue > 0)
                            <button type="button" @click="payOpen = !payOpen" class="w-full text-center px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg">
                                {{ __('Pay supplier') }}
                            </button>
                            <form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="space-y-3 mt-3" x-show="payOpen" style="display: none" x-cloak>
                                @csrf
                                <input type="hidden" name="mode" :value="mode">
                                <div>
                                    <x-input-label :value="__('Amount paid')" />
                                    <x-text-input name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-full" x-model.number="amount" required />
                                    <button type="button" class="text-xs text-brand-600 hover:underline mt-1" @click="amount = {{ $balanceDue }}">
                                        {{ __('Pay full') }} &#8377;{{ number_format($balanceDue, 2) }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                                        <button type="button" @click="mode = '{{ $value }}'"
                                            class="px-2 py-1.5 text-sm font-medium rounded-md border-2"
                                            :class="mode === '{{ $value }}' ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                                <x-primary-button class="w-full justify-center">{{ __('Save payment') }}</x-primary-button>
                            </form>
                        @else
                            <div class="text-center text-sm text-green-600 font-medium">{{ __('Nothing due — fully paid.') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="hidden lg:block space-y-4 mt-0 print:hidden">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-800">{{ __('Pay supplier') }}</h3>
                            <span @class([
                                'px-2.5 py-1 rounded-full text-xs font-semibold',
                                'bg-green-50 text-green-700' => $purchase->status === 'paid',
                                'bg-amber-50 text-amber-700' => $purchase->status === 'partial',
                                'bg-red-50 text-red-600' => $purchase->status === 'unpaid',
                            ])>
                                {{ $purchase->status === 'paid' ? __('Fully paid') : ($purchase->status === 'partial' ? $paymentPct.'% '.__('paid') : __('Unpaid')) }}
                            </span>
                        </div>

                        <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden mb-4">
                            <div class="h-full {{ $purchase->status === 'paid' ? 'bg-green-500' : 'bg-brand-500' }}" style="width: {{ $paymentPct }}%"></div>
                        </div>

                        @if ($balanceDue > 0)
                            <form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="mode" :value="mode">
                                <div>
                                    <x-input-label for="amount" :value="__('Amount paid')" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-full" x-model.number="amount" value="{{ old('amount') }}" required />
                                    <button type="button" class="text-xs text-brand-600 hover:underline mt-1" @click="amount = {{ $balanceDue }}">
                                        {{ __('Pay full') }} &#8377;{{ number_format($balanceDue, 2) }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                                        <button type="button" @click="mode = '{{ $value }}'"
                                            class="px-2 py-1.5 text-sm font-medium rounded-md border-2"
                                            :class="mode === '{{ $value }}' ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                                <x-primary-button class="w-full justify-center">{{ __('Save payment') }}</x-primary-button>
                            </form>
                        @else
                            <button type="button" disabled class="w-full text-center px-4 py-2.5 bg-gray-50 text-gray-400 text-sm font-semibold rounded-lg cursor-not-allowed">
                                {{ __('Record payment (nothing due)') }}
                            </button>
                        @endif

                        @if ($purchase->payments->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('Paid so far') }}</p>
                                <div class="space-y-2.5 max-h-56 overflow-y-auto">
                                    @foreach ($purchase->payments as $payment)
                                        <div class="flex justify-between text-sm">
                                            <div>
                                                <div class="text-gray-800">{{ $payment->note ?: __('Payment') }}{{ $payment->mode ? ' · '.ucfirst($payment->mode) : '' }}</div>
                                                <div class="text-xs text-gray-400">{{ $payment->created_at->format('d M, h:i A') }} &middot; {{ $payment->user->name ?? '—' }}</div>
                                            </div>
                                            <span class="font-medium text-gray-800 shrink-0">&#8377;{{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank"
                                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }})"
                                class="inline-flex items-center justify-center gap-1.5 px-2 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                {{ __('PDF') }}
                            </a>
                            <button onclick="window.print()" class="inline-flex items-center justify-center gap-1.5 px-2 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25C7.629 2.25 7.125 2.754 7.125 3.375v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                                {{ __('Print') }}
                            </button>
                            <button type="button"
                                onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('purchases.pdf', $purchase)) }}, filename: {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Purchase').' '.$purchase->purchase_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                                class="inline-flex items-center justify-center gap-1.5 px-2 py-2 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                                {{ __('Share') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-2 transform-gpu print:hidden" style="padding-bottom: env(safe-area-inset-bottom);">
            <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank"
                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }})"
                class="w-11 h-11 flex items-center justify-center border border-gray-300 text-gray-700 rounded-lg shrink-0" title="{{ __('PDF') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            </a>
            <button type="button"
                onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('purchases.pdf', $purchase)) }}, filename: {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Purchase').' '.$purchase->purchase_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                class="w-11 h-11 flex items-center justify-center border border-green-200 text-green-600 rounded-lg shrink-0" title="{{ __('Share') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
            </button>
            @if ($balanceDue > 0)
                <button type="button" @click="payOpen = true; $el.closest('div.py-6').querySelector('[x-show=\'payOpen\']')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                    {{ __('Pay supplier') }}
                </button>
            @else
                <div class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-green-50 text-green-700 text-sm font-semibold rounded-lg">
                    {{ __('Fully paid') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
