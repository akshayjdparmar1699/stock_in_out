<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight truncate">{{ __('Invoice') }} {{ $invoice->invoice_number }}</h2>
            <div class="flex flex-wrap gap-2 print:hidden">
                <a href="{{ route('invoices.edit', $invoice) }}" onclick="window.showCubeLoader()"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    {{ __('Edit') }}
                </a>
                <button type="button"
                    onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('invoices.pdf', $invoice)) }}, filename: {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Invoice').' '.$invoice->invoice_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                    class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                    <span>{{ __('Share') }}</span>
                </button>
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                    onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($invoice->invoice_number.'.pdf') }})"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    {{ __('Download PDF') }}
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    {{ __('Print') }}
                </button>
            </div>
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

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($lowStockAdminUrl)
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
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

            <div class="bg-white shadow-sm rounded-lg border border-gray-300 p-8">

                <div class="flex justify-between mb-8">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">{{ $invoice->branch->name }}</div>
                        <div class="text-sm text-gray-500">{{ $invoice->branch->address }}</div>
                        <div class="text-sm text-gray-500">{{ $invoice->branch->phone }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold tracking-wide text-indigo-600 uppercase mb-1">{{ __('Invoice') }}</div>
                        <div class="text-sm text-gray-500">{{ __('Invoice #') }}</div>
                        <div class="font-medium text-gray-800">{{ $invoice->invoice_number }}</div>
                        <div class="text-sm text-gray-500 mt-2">{{ __('Date') }}</div>
                        <div class="font-medium text-gray-800">{{ $invoice->invoice_date->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="mb-8">
                    <div class="text-sm text-gray-500">{{ __('Bill To') }}</div>
                    <div class="font-medium text-gray-800">{{ $invoice->customer->name }}</div>
                    <div class="text-sm text-gray-500">{{ $invoice->customer->phone }}</div>
                    @if ($invoice->customer->address)
                        <div class="text-sm text-gray-500">{{ $invoice->customer->address }}</div>
                    @endif
                    @if ($invoice->customer->gst_number)
                        <div class="text-sm text-gray-500">{{ __('GSTIN') }}: {{ $invoice->customer->gst_number }}</div>
                    @endif
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="w-full border border-gray-300 border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                                <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                                <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Rate') }}</th>
                                <th class="border border-gray-300 px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $line)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800">{{ $line->item->name }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-600 text-right">{{ $line->quantity }} {{ $line->item->unit }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-600 text-right">₹{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-800 text-right">₹{{ number_format($line->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @php
                    $previousDue = $customerDue - ($invoice->total - $invoice->paid_amount);
                @endphp
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-6">
                    <div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">{{ __('Scan & Pay') }}</div>
                        <img src="{{ asset('images/payment-qr.png') }}" alt="{{ __('Payment QR code') }}" class="w-32 h-32">
                        <div class="mt-2 text-xs text-gray-500 leading-relaxed">
                            <div class="font-medium text-gray-700">HDFC Bank</div>
                            <div>{{ __('A/c No') }}: 50200097420397</div>
                            <div>{{ __('A/c Holder') }}: Om Sai Aalubhandar</div>
                            <div>IFSC: HDFC0004196</div>
                        </div>
                    </div>
                    <div class="w-72 space-y-1">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span>₹{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Discount') }}</span>
                            <span>- ₹{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Tax') }}</span>
                            <span>+ ₹{{ number_format($invoice->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Transportation') }}</span>
                            <span>+ ₹{{ number_format($invoice->transportation, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-gray-900 border-t pt-1">
                            <span>{{ __('Total') }}</span>
                            <span>₹{{ number_format($invoice->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Paid') }}</span>
                            <span>₹{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-sm text-gray-500 border-t pt-1 mt-1">
                            <span>{{ __('Previous Due') }}</span>
                            <span class="{{ $previousDue > 0 ? 'text-red-600' : ($previousDue < 0 ? 'text-green-600' : 'text-gray-700') }}">
                                ₹{{ number_format(abs($previousDue), 2) }}{{ $previousDue < 0 ? ' CR' : '' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-base font-semibold {{ $customerDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                            <span>{{ $customerDue > 0 ? __('Balance Due') : __('Settled / In Credit') }}</span>
                            <span>₹{{ number_format(abs($customerDue), 2) }}{{ $customerDue < 0 ? ' CR' : '' }}</span>
                        </div>
                    </div>
                </div>

                @if ($invoice->notes)
                    <div class="mt-6 text-sm text-gray-500">
                        <div class="font-medium text-gray-700">{{ __('Notes') }}</div>
                        {{ $invoice->notes }}
                    </div>
                @endif

                <div class="mt-8 text-xs text-gray-400">
                    {{ __('Billed by') }} {{ $invoice->user->name ?? '—' }}
                </div>
            </div>

            <div class="mt-6 bg-white shadow-sm rounded-lg p-6 print:hidden">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Record a Payment') }}</h3>

                @if ($invoice->balanceDue() > 0)
                    <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <div>
                            <x-input-label for="amount" :value="__('Amount Received')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-40" value="{{ old('amount') }}" required />
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <x-input-label for="note" :value="__('Note (optional)')" />
                            <x-text-input id="note" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Cash, UPI, part payment') }}" />
                        </div>
                        <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-green-600">{{ __('This invoice is fully paid.') }}</p>
                @endif

                @if ($invoice->payments->isNotEmpty())
                    <div class="mt-6 border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-3">{{ __('Payment History') }}</h4>
                        <div class="space-y-2">
                            @foreach ($invoice->payments as $payment)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <span class="text-gray-700">{{ $payment->created_at->format('d M Y, h:i A') }}</span>
                                        @if ($payment->note)
                                            <span class="text-gray-400">— {{ $payment->note }}</span>
                                        @endif
                                        <span class="text-gray-400">({{ $payment->user->name ?? '—' }})</span>
                                    </div>
                                    <span class="font-medium text-gray-800">₹{{ number_format($payment->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
