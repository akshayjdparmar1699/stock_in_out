<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight truncate">{{ __('Purchase') }} {{ $purchase->purchase_number }}</h2>
            <div class="flex flex-wrap gap-2 print:hidden">
                <button type="button"
                    onclick="window.sharePdf({ url: {{ \Illuminate\Support\Js::from(route('purchases.pdf', $purchase)) }}, filename: {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }}, title: {{ \Illuminate\Support\Js::from(__('Purchase').' '.$purchase->purchase_number) }}, text: {{ \Illuminate\Support\Js::from($shareMessage) }} })"
                    class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.933 2.185 2.25 2.25 0 00-3.933-2.185zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                    <span>{{ __('Share') }}</span>
                </button>
                <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank"
                    onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($purchase->purchase_number.'.pdf') }})"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    {{ __('Download PDF') }}
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    {{ __('Print') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8">

                <div class="flex justify-between mb-8">
                    <div>
                        <div class="text-lg font-semibold text-gray-800">{{ $purchase->branch->name }}</div>
                        <div class="text-sm text-gray-500">{{ __('Purchase recorded by') }} {{ $purchase->user->name ?? '—' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ __('Purchase #') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchase->purchase_number }}</div>
                        <div class="text-sm text-gray-500 mt-2">{{ __('Date') }}</div>
                        <div class="font-medium text-gray-800">{{ $purchase->purchase_date->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="mb-8">
                    <div class="text-sm text-gray-500">{{ __('Supplier') }}</div>
                    <div class="font-medium text-gray-800">{{ $purchase->supplier->name }}</div>
                    <div class="text-sm text-gray-500">{{ $purchase->supplier->phone }}</div>
                    @if ($purchase->supplier->address)
                        <div class="text-sm text-gray-500">{{ $purchase->supplier->address }}</div>
                    @endif
                    @if ($purchase->supplier->gst_number)
                        <div class="text-sm text-gray-500">{{ __('GSTIN') }}: {{ $purchase->supplier->gst_number }}</div>
                    @endif
                </div>

                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Item') }}</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Cost/Unit') }}</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($purchase->items as $line)
                                <tr>
                                    <td class="py-2 text-sm text-gray-800">{{ $line->item->name }}</td>
                                    <td class="py-2 text-sm text-gray-600 text-right">{{ $line->quantity }} {{ $line->item->unit }}</td>
                                    <td class="py-2 text-sm text-gray-600 text-right">₹{{ number_format($line->unit_cost, 2) }}</td>
                                    <td class="py-2 text-sm text-gray-800 text-right">₹{{ number_format($line->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @php
                    $previousDue = $supplierDue - ($purchase->total - $purchase->paid_amount);
                @endphp
                <div class="flex justify-end">
                    <div class="w-72 space-y-1">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span>₹{{ number_format($purchase->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Discount') }}</span>
                            <span>- ₹{{ number_format($purchase->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Tax') }}</span>
                            <span>+ ₹{{ number_format($purchase->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-gray-900 border-t pt-1">
                            <span>{{ __('Total') }}</span>
                            <span>₹{{ number_format($purchase->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>{{ __('Paid') }}</span>
                            <span>₹{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>

                        <div class="flex justify-between text-sm text-gray-500 border-t pt-1 mt-1">
                            <span>{{ __('Previous Due') }}</span>
                            <span class="{{ $previousDue > 0 ? 'text-red-600' : ($previousDue < 0 ? 'text-green-600' : 'text-gray-700') }}">
                                ₹{{ number_format(abs($previousDue), 2) }}{{ $previousDue < 0 ? ' CR' : '' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-base font-semibold {{ $supplierDue > 0 ? 'text-red-600' : 'text-green-600' }}">
                            <span>{{ $supplierDue > 0 ? __('Balance Due') : __('Settled / In Credit') }}</span>
                            <span>₹{{ number_format(abs($supplierDue), 2) }}{{ $supplierDue < 0 ? ' CR' : '' }}</span>
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

            <div class="mt-6 bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-700 mb-4">{{ __('Record a Payment to Supplier') }}</h3>

                @if ($purchase->balanceDue() > 0)
                    <form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <div>
                            <x-input-label for="amount" :value="__('Amount Paid')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 w-40" value="{{ old('amount') }}" required />
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <x-input-label for="note" :value="__('Note (optional)')" />
                            <x-text-input id="note" name="note" type="text" class="mt-1 w-full" value="{{ old('note') }}" placeholder="{{ __('e.g. Cash, UPI, part payment') }}" />
                        </div>
                        <x-primary-button>{{ __('Record Payment') }}</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-green-600">{{ __('This purchase is fully paid.') }}</p>
                @endif

                @if ($purchase->payments->isNotEmpty())
                    <div class="mt-6 border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-3">{{ __('Payment History') }}</h4>
                        <div class="space-y-2">
                            @foreach ($purchase->payments as $payment)
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
