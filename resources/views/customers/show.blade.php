@php
    $limit = (float) $customer->credit_limit;
    $isOver = $customer->isOverCreditLimit();
    $limitRatio = $limit > 0 ? (max($due, 0) / $limit) : 0;
    $limitPct = $limit > 0 ? min(round($limitRatio * 100), 100) : 0;
    $barColor = $isOver ? 'bg-red-500' : ($limitRatio >= 0.8 ? 'bg-amber-400' : 'bg-brand-400');
    $ledgerEntries = $entries;
    $invoiceEntries = $entries->where('type', 'billed')->values();
    $paymentEntries = $entries->whereIn('type', ['received', 'refund'])->values();
    $avatarPalette = [
        'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700', 'bg-pink-100 text-pink-700', 'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700',
    ];
    $initials = collect(explode(' ', trim($customer->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $avatarClass = $avatarPalette[$customer->id % count($avatarPalette)];
@endphp
<x-app-layout>
    <x-slot name="header">
        <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400 mb-1">
            <a href="{{ route('customers.index') }}" class="hover:text-gray-600">{{ __('Customers') }}</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">{{ $customer->name }}</span>
        </nav>

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('customers.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </a>
                <p class="font-semibold text-base lg:hidden text-gray-800">{{ __('Customer') }}</p>
            </div>
            <a href="{{ route('customers.edit', $customer) }}" class="lg:hidden shrink-0 text-gray-500 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            </a>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-md hover:bg-brand-700">
                    {{ __('+ New Bill') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{
            deleteOpen: false, deleteForm: null, deleteLabel: '',
            activeTab: 'ledger',
            payModalOpen: false,
        }"
        x-init="
            const url = {{ Illuminate\Support\Js::from(session('open_whatsapp_url')) }};
            if (url) { window.open(url, '_blank'); }
        ">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 lg:space-y-6">

            {{-- Desktop identity card --}}
            <div class="hidden lg:flex bg-white shadow-sm rounded-lg p-6 items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <span class="w-16 h-16 rounded-full flex items-center justify-center text-lg font-semibold shrink-0 {{ $avatarClass }}">{{ $initials }}</span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-xl text-gray-900 truncate">{{ $customer->name }}</h2>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide {{ $customer->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $customer->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500 mt-1.5">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                {{ $customer->phone }}
                            </span>
                            @if ($customer->address)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    {{ $customer->address }}
                                </span>
                            @endif
                            @if ($customer->branches->isNotEmpty())
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                    {{ $customer->branches->pluck('name')->implode(', ') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('customers.statement-pdf', $customer) }}" target="_blank"
                    onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($customer->name.' - Statement.pdf') }})"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    {{ __('Statement PDF') }}
                </a>
            </div>

            {{-- Mobile identity card --}}
            <div class="lg:hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
                <span class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarClass }}">{{ $initials }}</span>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $customer->name }}</p>
                    <p class="text-sm text-gray-400">{{ $customer->address ?: '—' }} &middot; {{ $customer->phone }}</p>
                </div>
            </div>

            {{-- Mobile balance card --}}
            <div class="lg:hidden bg-purple-50 border border-purple-100 rounded-2xl p-4">
                <p class="text-sm text-gray-500">{{ $due > 0 ? __('Balance due') : __('Settled / In Credit') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">
                    &#8377;{{ number_format(abs($due), 0) }}{{ $due < 0 ? ' CR' : '' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    @if ($lastBillDate)
                        {{ __('Last bill') }} {{ $lastBillDate->format('d M') }}
                    @endif
                    @if ($lastBillDate && $lastPaymentDate)
                        &middot;
                    @endif
                    @if ($lastPaymentDate)
                        {{ __('Last payment') }} {{ $lastPaymentDate->format('d M') }}
                    @endif
                </p>
            </div>

            <a href="{{ route('customers.statement-pdf', $customer) }}" target="_blank"
                onclick="event.preventDefault(); window.downloadPdf(this.href, {{ \Illuminate\Support\Js::from($customer->name.' - Statement.pdf') }})"
                class="lg:hidden flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                {{ __('Statement PDF') }}
            </a>

            {{-- Mobile mini stats --}}
            <div class="lg:hidden grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Billed in') }} {{ $thisMonthLabel }}</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">&#8377;{{ number_format($billedThisMonth, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-3">
                    <p class="text-xs text-gray-400">{{ __('Received in') }} {{ $thisMonthLabel }}</p>
                    <p class="text-base font-bold text-green-600 mt-0.5">&#8377;{{ number_format($receivedThisMonth, 0) }}</p>
                </div>
            </div>

            {{-- Desktop stat cards --}}
            <div class="hidden lg:grid lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ $due > 0 ? __('Balance due') : __('Settled / In Credit') }}</p>
                    <p class="text-2xl font-bold {{ $due > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">&#8377;{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}</p>
                    @if ($due > 0 && $limit > 0)
                        <div class="relative h-1.5 rounded-full bg-gray-100 overflow-hidden mt-2.5">
                            <div class="absolute inset-y-0 left-0 {{ $barColor }}" style="width: {{ $limitPct }}%"></div>
                        </div>
                        @if ($isOver)
                            <p class="text-xs text-red-500 font-medium mt-1.5">{{ __('Over limit by') }} &#8377;{{ number_format($due - $limit, 0) }}</p>
                        @endif
                    @endif
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Credit limit') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">&#8377;{{ number_format($limit, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ __('Set while adding customer') }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Billed in') }} {{ $thisMonthLabel }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">&#8377;{{ number_format($billedThisMonth, 0) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Received in') }} {{ $thisMonthLabel }}</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">&#8377;{{ number_format($receivedThisMonth, 0) }}</p>
                </div>
            </div>

            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="flex items-center gap-1 px-2 pt-2 border-b border-gray-100">
                        @foreach (['ledger' => [__('Ledger'), __('Ledger')], 'invoices' => [__('Bills'), __('Invoices')], 'payments' => [__('Payments'), __('Payments')]] as $tab => $labels)
                            <button type="button" @click="activeTab = '{{ $tab }}'"
                                class="px-3.5 py-2.5 text-sm font-medium border-b-2 -mb-px"
                                :class="activeTab === '{{ $tab }}' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                                <span class="lg:hidden">{{ $labels[0] }}</span>
                                <span class="hidden lg:inline">{{ $labels[1] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div x-show="activeTab === 'ledger'" class="divide-y divide-gray-100">
                        @include('customers.partials.ledger-rows', ['entries' => $ledgerEntries, 'customer' => $customer])
                    </div>
                    <div x-show="activeTab === 'invoices'" style="display: none" class="divide-y divide-gray-100">
                        @include('customers.partials.ledger-rows', ['entries' => $invoiceEntries, 'customer' => $customer])
                    </div>
                    <div x-show="activeTab === 'payments'" style="display: none" class="divide-y divide-gray-100">
                        @include('customers.partials.ledger-rows', ['entries' => $paymentEntries, 'customer' => $customer])
                    </div>
                </div>

                <div class="hidden lg:block space-y-6 mt-0">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        @include('customers.partials.payment-form', ['customer' => $customer])
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-800 mb-3">{{ __('Details') }}</h3>
                        <dl class="space-y-2.5 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-400">{{ __('Customer since') }}</dt>
                                <dd class="text-gray-700">{{ $customer->created_at->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-400">{{ __('GSTIN') }}</dt>
                                <dd class="text-gray-700">{{ $customer->gst_number ?: __('Not added') }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-400 shrink-0">{{ __('Usual items') }}</dt>
                                <dd class="text-gray-700 text-right">{{ $usualItems ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="button" @click="payModalOpen = true" class="flex-1 px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg text-center">
                {{ __('Receive payment') }}
            </button>
            <a href="{{ route('invoices.create') }}" class="flex-1 px-4 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-lg text-center">
                {{ __('+ New Bill') }}
            </a>
        </div>

        {{-- Mobile payment modal --}}
        <div x-show="payModalOpen" style="display: none" class="lg:hidden fixed inset-0 z-50 flex items-end">
            <div x-show="payModalOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-900/50" @click="payModalOpen = false"></div>
            <div x-show="payModalOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-6"
                class="relative bg-white rounded-t-2xl w-full p-6 max-h-[85vh] overflow-y-auto">
                @include('customers.partials.payment-form', ['customer' => $customer])
            </div>
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
