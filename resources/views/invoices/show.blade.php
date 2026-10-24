@php
    $previousDue = $invoice->isGuestCustomer() ? null : $customerDue - ($invoice->total - $invoice->paid_amount);
    $paymentPct = $invoice->total > 0 ? min(100, round(($invoice->paid_amount / $invoice->total) * 100)) : 100;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" onclick="window.showCubeLoader()" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-2 text-sm text-gray-400">
                    <a href="{{ route('invoices.index') }}" class="hover:text-gray-600">{{ __('Invoices') }}</a>
                    <span>/</span>
                    <span class="text-gray-800 font-semibold">{{ $invoice->invoice_number }}</span>
                    <span @class([
                        'px-2 py-0.5 rounded-full text-xs font-medium uppercase',
                        'bg-green-50 text-green-700' => $invoice->status === 'paid',
                        'bg-amber-50 text-amber-700' => $invoice->status === 'partial',
                        'bg-red-50 text-red-600' => $invoice->status === 'unpaid',
                    ])>{{ $invoice->status }}</span>
                </nav>
                <h2 class="font-semibold text-lg text-gray-800 leading-tight truncate lg:hidden">{{ $invoice->invoice_number }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 print:hidden shrink-0">
                <a href="{{ route('invoices.edit', $invoice) }}" onclick="window.showCubeLoader()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('invoices.create') }}" onclick="window.showCubeLoader()"
                    class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">
                    {{ __('+ New Bill') }}
                </a>
            </div>
            <a href="{{ route('invoices.edit', $invoice) }}" class="lg:hidden shrink-0 text-gray-500 p-1 print:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            </a>
        </div>
    </x-slot>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.prefetchPdf?.(
                {{ \Illuminate\Support\Js::from(route('invoices.pdf', $invoice)) }},
                {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }}
            );
        });
    </script>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{ guestWarningOpen: {{ session('guest_credit_warning') ? 'true' : 'false' }}, payOpen: false, mode: 'cash' }"
        x-init="
            const url = {{ Illuminate\Support\Js::from(session('open_whatsapp_url')) }};
            if (url) { window.open(url, '_blank'); }
        ">
        @if (session('guest_credit_warning'))
            <div x-show="guestWarningOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 print:hidden" style="display: none;">
                <div class="absolute inset-0 bg-gray-900/50" @click="guestWarningOpen = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center">
                    <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-5 bg-amber-50">
                        <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-amber-700">{{ __('Remember this one yourself') }}</h3>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ __("This bill wasn't paid in full and no customer record was saved — we have no name, phone, or way to track that money is still owed. You'll need to remember it yourself.") }}
                    </p>
                    <button type="button" @click="guestWarningOpen = false"
                        class="mt-7 w-full px-4 py-2.5 rounded-lg text-white text-sm font-medium bg-amber-600 hover:bg-amber-700">
                        {{ __('Got it') }}
                    </button>
                </div>
            </div>
        @endif

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if ($lowStockAdminUrl)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <div>
                        <p class="text-sm font-medium text-red-800">{{ __('Stock is now low for items in this bill:') }}</p>
                        <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                            @foreach ($lowStockLines as $line)
                                <li>{{ $line['name'] }}: {{ $line['quantity'] }} {{ $line['unit'] }} {{ __('left') }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ $lowStockAdminUrl }}" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                        {{ __('Notify Admin on WhatsApp') }}
                    </a>
                </div>
            @endif

            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                {{-- Invoice card --}}
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-100 lg:border-gray-200 p-5 lg:p-8">

                    {{-- Mobile summary --}}
                    <div class="lg:hidden text-center pb-5 border-b border-gray-100 mb-5">
                        <span @class([
                            'inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase mb-3',
                            'bg-green-50 text-green-700' => $invoice->status === 'paid',
                            'bg-amber-50 text-amber-700' => $invoice->status === 'partial',
                            'bg-red-50 text-red-600' => $invoice->status === 'unpaid',
                        ])>
                            {{ $invoice->status }} @if ($invoice->status === 'paid') &middot; {{ __('Settled') }}@endif
                        </span>
                        <div class="text-3xl font-bold text-gray-900">&#8377;{{ number_format($invoice->total, 2) }}</div>
                        <div class="mt-2 text-sm font-medium text-gray-800">{{ $invoice->displayCustomerName() }}</div>
                        <div class="text-xs text-gray-400">
                            {{ $invoice->customer->phone ?? '' }}{{ $invoice->customer ? ' · ' : '' }}{{ $invoice->invoice_date->format('d M Y') }}, {{ $invoice->created_at->format('h:i A') }}
                        </div>
                    </div>

                    <div class="hidden lg:flex justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <x-application-logo class="w-11 h-11 rounded-xl shrink-0" />
                            <div>
                                <div class="text-base font-semibold text-gray-900">{{ $invoice->branch->name }}</div>
                                <div class="text-sm text-gray-400">{{ $invoice->branch->address ?: '' }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-semibold tracking-wide text-brand-600 uppercase mb-1">{{ __('Invoice') }}</div>
                            <div class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</div>
                            <div class="text-sm text-gray-400 mt-1">{{ $invoice->invoice_date->format('d M Y') }}, {{ $invoice->created_at->format('h:i A') }}</div>
                        </div>
                    </div>

                    <div class="hidden lg:grid lg:grid-cols-2 lg:gap-6 mb-6">
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('Bill to') }}</div>
                            <div class="font-medium text-gray-900 mt-1 flex items-center gap-2">
                                {{ $invoice->displayCustomerName() }}
                                @if ($invoice->isGuestCustomer())
                                    <span class="text-xs font-normal px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ __('Walk-in') }}</span>
                                @endif
                            </div>
                            @if ($invoice->customer)
                                <div class="text-sm text-gray-500">{{ $invoice->customer->phone }}{{ $invoice->customer->gst_number ? ' · GSTIN '.$invoice->customer->gst_number : '' }}</div>
                            @endif
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('Billed by') }}</div>
                            <div class="font-medium text-gray-900 mt-1">{{ $invoice->user->name ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="mb-6">
                        <p class="lg:hidden text-xs font-semibold tracking-wide text-gray-400 uppercase mb-2">{{ __('Items') }}</p>
                        <table class="w-full hidden lg:table">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="py-2 text-left text-xs font-medium text-gray-400 uppercase">{{ __('Item') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Qty') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Rate') }}</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-400 uppercase">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($invoice->items as $line)
                                    <tr>
                                        <td class="py-2.5 text-sm text-gray-800">{{ $line->item->name }}</td>
                                        <td class="py-2.5 text-sm text-gray-600 text-right">{{ $line->quantity }} {{ $line->displayUnit() }}</td>
                                        <td class="py-2.5 text-sm text-gray-600 text-right">&#8377;{{ number_format($line->unit_price, 2) }}</td>
                                        <td class="py-2.5 text-sm text-gray-900 text-right font-medium">&#8377;{{ number_format($line->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="lg:hidden divide-y divide-gray-50 border-t border-gray-50">
                            @foreach ($invoice->items as $line)
                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm text-gray-800 truncate">{{ $line->item->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $line->quantity }} {{ $line->displayUnit() }} &times; &#8377;{{ number_format($line->unit_price, 2) }}</div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900 shrink-0">&#8377;{{ number_format($line->total, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-6">
                        @if ($invoice->branch->hasPaymentDetails())
                            <div class="print:block">
                                <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">{{ __('Scan and pay') }}</div>
                                <img src="{{ $invoice->branch->paymentQrUrl() }}" alt="{{ __('Payment QR code') }}" class="w-28 h-28 rounded-lg border border-gray-100">
                                <div class="mt-2 text-xs text-gray-500 leading-relaxed">
                                    <div class="font-medium text-gray-700">{{ $invoice->branch->paymentBankName() }}</div>
                                    <div>{{ __('A/c') }} {{ $invoice->branch->paymentAccountNumber() }}</div>
                                    <div>{{ $invoice->branch->paymentAccountHolderName() }}</div>
                                    <div>IFSC {{ $invoice->branch->paymentIfscCode() }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="w-full sm:w-72 space-y-1">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Subtotal') }}</span>
                                <span>&#8377;{{ number_format($invoice->subtotal, 2) }}</span>
                            </div>
                            @if ($invoice->discount > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Discount') }}</span>
                                    <span>- &#8377;{{ number_format($invoice->discount, 2) }}</span>
                                </div>
                            @endif
                            @if ($invoice->tax > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Tax') }}</span>
                                    <span>+ &#8377;{{ number_format($invoice->tax, 2) }}</span>
                                </div>
                            @endif
                            @if ($invoice->transportation > 0)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Transportation') }}</span>
                                    <span>+ &#8377;{{ number_format($invoice->transportation, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-100 pt-1.5 mt-1.5">
                                <span>{{ __('Total') }}</span>
                                <span>&#8377;{{ number_format($invoice->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Paid') }}</span>
                                <span>&#8377;{{ number_format($invoice->paid_amount, 2) }}</span>
                            </div>

                            @if ($invoice->isGuestCustomer())
                                <div class="flex justify-between text-sm font-bold rounded-md px-3 py-2 mt-1 {{ $invoice->balanceDue() > 0 ? 'text-red-700 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                    <span>{{ $invoice->balanceDue() > 0 ? __('Balance') : __('Settled') }}</span>
                                    <span>&#8377;{{ number_format($invoice->balanceDue(), 2) }}{{ $invoice->balanceDue() <= 0 ? ' · '.__('Settled') : '' }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm text-gray-500 border-t border-gray-100 pt-1.5 mt-1.5">
                                    <span>{{ __('Previous due') }}</span>
                                    <span class="{{ $previousDue > 0 ? 'text-red-600' : ($previousDue < 0 ? 'text-green-600' : 'text-gray-700') }}">
                                        &#8377;{{ number_format(abs($previousDue), 2) }}{{ $previousDue < 0 ? ' CR' : '' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm font-bold rounded-md px-3 py-2 mt-1 {{ $customerDue > 0 ? 'text-red-700 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                    <span>{{ $customerDue > 0 ? __('Balance') : __('Settled') }}</span>
                                    <span>&#8377;{{ number_format(abs($customerDue), 2) }}{{ $customerDue < 0 ? ' CR' : ($customerDue <= 0 ? ' · '.__('Settled') : '') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($invoice->notes)
                        <div class="mt-6 text-sm text-gray-500">
                            <div class="font-medium text-gray-700">{{ __('Notes') }}</div>
                            {{ $invoice->notes }}
                        </div>
                    @endif

                    <p class="mt-6 text-xs text-gray-400 print:hidden">{{ __('Discount, tax and transport rows show only when they are more than ₹0.') }}</p>
                </div>

                {{-- Mobile payment card --}}
                <div class="lg:hidden bg-white shadow-sm rounded-lg border border-gray-100 p-4 mt-4 print:hidden">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800">{{ __('Payment') }}</h3>
                        <span @class([
                            'px-2.5 py-1 rounded-full text-xs font-semibold',
                            'bg-green-50 text-green-700' => $invoice->status === 'paid',
                            'bg-amber-50 text-amber-700' => $invoice->status === 'partial',
                            'bg-red-50 text-red-600' => $invoice->status === 'unpaid',
                        ])>
                            {{ $invoice->status === 'paid' ? __('Fully paid') : ($invoice->status === 'partial' ? __('Partially paid') : __('Unpaid')) }}
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-500 mb-1">
                        <span>{{ __('Received') }}</span>
                        <span class="font-medium text-gray-800">&#8377;{{ number_format($invoice->paid_amount, 2) }} {{ __('of') }} &#8377;{{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full {{ $invoice->status === 'paid' ? 'bg-green-500' : 'bg-brand-500' }}" style="width: {{ $paymentPct }}%"></div>
                    </div>

                    @if ($invoice->payments->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2.5">
                            @foreach ($invoice->payments as $payment)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <div class="text-gray-800">{{ $payment->note ?: __('Payment recorded') }}{{ $payment->mode ? ' · '.ucfirst($payment->mode) : '' }}</div>
                                        <div class="text-xs text-gray-400">{{ $payment->created_at->format('d M, h:i A') }} &middot; {{ $payment->user->name ?? '—' }}</div>
                                    </div>
                                    <span class="font-medium text-gray-800 shrink-0">&#8377;{{ number_format($payment->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        @if ($invoice->balanceDue() > 0 || ($invoice->customer && $customerDue > 0))
                            <button type="button" @click="payOpen = !payOpen" class="w-full text-center px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg">
                                {{ __('Record payment') }}
                            </button>
                            <form method="POST"
                                action="{{ $invoice->balanceDue() > 0 ? route('invoices.payments.store', $invoice) : route('customers.payments.store', $invoice->customer) }}"
                                class="space-y-3 mt-3" x-show="payOpen" style="display: none" x-cloak>
                                @csrf
                                <input type="hidden" name="mode" :value="mode">
                                <div>
                                    <x-input-label :value="__('Amount received')" />
                                    <x-text-input name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-full" required />
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
                                <x-text-input name="note" type="text" class="w-full" placeholder="{{ __('Note (optional)') }}" />
                                <x-primary-button class="w-full justify-center">{{ __('Record payment') }}</x-primary-button>
                                @if ($invoice->balanceDue() <= 0)
                                    <p class="text-xs text-gray-400 text-center">{{ __('This bill is already fully paid — this settles their other outstanding due instead.') }}</p>
                                @endif
                            </form>
                        @else
                            <div class="text-center text-sm text-green-600 font-medium">{{ __('Nothing due — fully paid.') }}</div>
                        @endif
                    </div>
                </div>

                @if ($invoice->customer)
                    <div class="lg:hidden bg-white shadow-sm rounded-lg border border-gray-100 p-4 print:hidden">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('Customer khata after this bill') }}</span>
                            <span class="font-semibold {{ $customerDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                                &#8377;{{ number_format(abs($customerDue), 0) }}{{ $customerDue < 0 ? ' CR' : ' '.__('due') }}
                            </span>
                        </div>
                        <a href="{{ route('customers.show', $invoice->customer) }}" class="block text-sm font-medium text-brand-600 hover:underline mt-2">
                            {{ __('Open') }} {{ $invoice->customer->name }}{{ __("'s khata") }} &rsaquo;
                        </a>
                    </div>
                @endif

                {{-- Sidebar --}}
                <div class="hidden lg:block space-y-4 mt-0 print:hidden">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <h3 class="font-semibold text-gray-800 mb-3">{{ __('Send this bill') }}</h3>
                        <button type="button"
                            onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('invoices.pdf', $invoice)) }}, filename: {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Invoice').' '.$invoice->invoice_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                            {{ __('Share on WhatsApp') }}
                        </button>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }})"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                {{ __('PDF') }}
                            </a>
                            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25C7.629 2.25 7.125 2.754 7.125 3.375v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                                {{ __('Print') }}
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-800">{{ __('Payment') }}</h3>
                            <span @class([
                                'px-2.5 py-1 rounded-full text-xs font-semibold',
                                'bg-green-50 text-green-700' => $invoice->status === 'paid',
                                'bg-amber-50 text-amber-700' => $invoice->status === 'partial',
                                'bg-red-50 text-red-600' => $invoice->status === 'unpaid',
                            ])>
                                {{ $invoice->status === 'paid' ? __('Fully paid') : ($invoice->status === 'partial' ? __('Partially paid') : __('Unpaid')) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm text-gray-500 mb-1">
                            <span>{{ __('Received') }}</span>
                            <span class="font-medium text-gray-800">&#8377;{{ number_format($invoice->paid_amount, 2) }} {{ __('of') }} &#8377;{{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full {{ $invoice->status === 'paid' ? 'bg-green-500' : 'bg-brand-500' }}" style="width: {{ $paymentPct }}%"></div>
                        </div>

                        @if ($invoice->payments->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ __('History') }}</p>
                                <div class="space-y-2.5 max-h-56 overflow-y-auto">
                                    @foreach ($invoice->payments as $payment)
                                        <div class="flex justify-between text-sm">
                                            <div>
                                                <div class="text-gray-800">{{ $payment->note ?: __('Payment recorded') }}{{ $payment->mode ? ' · '.ucfirst($payment->mode) : '' }}</div>
                                                <div class="text-xs text-gray-400">{{ $payment->created_at->format('d M, h:i A') }} &middot; {{ $payment->user->name ?? '—' }}</div>
                                            </div>
                                            <span class="font-medium text-gray-800 shrink-0">&#8377;{{ number_format($payment->amount, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            @if ($invoice->balanceDue() > 0 || ($invoice->customer && $customerDue > 0))
                                <button type="button" @click="payOpen = !payOpen" class="w-full text-center px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg">
                                    {{ __('Record payment') }}
                                </button>
                                <form method="POST"
                                    action="{{ $invoice->balanceDue() > 0 ? route('invoices.payments.store', $invoice) : route('customers.payments.store', $invoice->customer) }}"
                                    class="space-y-3 mt-3" x-show="payOpen" style="display: none" x-cloak>
                                    @csrf
                                    <input type="hidden" name="mode" :value="mode">
                                    <div>
                                        <x-input-label for="amount" :value="__('Amount received')" />
                                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-full" value="{{ old('amount') }}" required />
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
                                    <x-text-input name="note" type="text" class="w-full" value="{{ old('note') }}" placeholder="{{ __('Note (optional)') }}" />
                                    <x-primary-button class="w-full justify-center">{{ __('Record payment') }}</x-primary-button>
                                    @if ($invoice->balanceDue() <= 0)
                                        <p class="text-xs text-gray-400 text-center">{{ __('This bill is already fully paid — this settles their other outstanding due instead.') }}</p>
                                    @endif
                                </form>
                            @else
                                <button type="button" disabled class="w-full text-center px-4 py-2.5 bg-gray-50 text-gray-400 text-sm font-semibold rounded-lg cursor-not-allowed">
                                    {{ __('Record payment (nothing due)') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($invoice->customer)
                        <div class="bg-white shadow-sm rounded-lg p-5">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('Customer khata after this bill') }}</span>
                                <span class="font-semibold {{ $customerDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    &#8377;{{ number_format(abs($customerDue), 0) }}{{ $customerDue < 0 ? ' CR' : ' '.__('due') }}
                                </span>
                            </div>
                            <a href="{{ route('customers.show', $invoice->customer) }}" class="block text-sm font-medium text-brand-600 hover:underline mt-2">
                                {{ __('Open') }} {{ $invoice->customer->name }}{{ __("'s khata") }} &rsaquo;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-2 transform-gpu print:hidden" style="padding-bottom: env(safe-area-inset-bottom);">
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }})"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                {{ __('PDF') }}
            </a>
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25C7.629 2.25 7.125 2.754 7.125 3.375v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" /></svg>
                {{ __('Print') }}
            </a>
            <button type="button"
                onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('invoices.pdf', $invoice)) }}, filename: {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Invoice').' '.$invoice->invoice_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                class="flex-[1.4] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                {{ __('Share') }}
            </button>
        </div>
    </div>
</x-app-layout>
