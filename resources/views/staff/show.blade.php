@php
    $avatarPalette = [
        'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700', 'bg-pink-100 text-pink-700', 'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700',
    ];
    $initials = collect(explode(' ', trim($staffMember->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $avatarClass = $avatarPalette[$staffMember->id % count($avatarPalette)];
    $isPartner = $staffMember->isPartner();
    $advanceThisMonth = $staffMember->advanceForMonth($month);
    $salaryPaidThisMonth = $staffMember->salaryPaidForMonth($month);
    $leftToPay = $staffMember->leftToPayForMonth($month);
    $nextMonthLabel = $month->copy()->addMonth()->format('1 M');
@endphp
<x-app-layout>
    <x-slot name="header">
        <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400 mb-1">
            <a href="{{ route('staff.index') }}" class="hover:text-gray-600">{{ __('Staff & Partners') }}</a>
            <span>/</span>
            <span class="text-gray-700 font-medium">{{ $staffMember->name }}</span>
        </nav>

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('staff.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </a>
                <p class="font-semibold text-base lg:hidden text-gray-800">{{ $isPartner ? __('Partner') : __('Staff') }}</p>
            </div>
            <a href="{{ route('staff.edit', $staffMember) }}" class="lg:hidden shrink-0 text-gray-500 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
            </a>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('staff.edit', $staffMember) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    {{ __('Edit details') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false, activeTab: 'all', giveModalOpen: false, giveMode: 'salary' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 lg:space-y-6">

            {{-- Desktop identity card --}}
            <div class="hidden lg:flex bg-white shadow-sm rounded-lg p-6 items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <span class="w-16 h-16 rounded-full flex items-center justify-center text-lg font-semibold shrink-0 {{ $avatarClass }}">{{ $initials }}</span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-xl text-gray-900 truncate">{{ $staffMember->name }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase {{ $isPartner ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">{{ $isPartner ? __('Partner') : __('Staff') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold uppercase {{ $staffMember->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $staffMember->is_active ? __('Active') : __('Inactive') }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500 mt-1.5">
                            <span>{{ $staffMember->phone ?: __('Phone not added') }}</span>
                            <span>{{ $staffMember->branches->pluck('name')->join(', ') }}</span>
                        </div>
                    </div>
                </div>
                @unless ($isPartner)
                    <div class="text-right shrink-0">
                        <div class="text-sm text-gray-500">{{ __('Monthly salary') }}</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $staffMember->monthly_salary ? '₹'.number_format($staffMember->monthly_salary, 0) : __('Not set') }}</div>
                    </div>
                @endunless
            </div>

            {{-- Mobile identity card --}}
            <div class="lg:hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
                <span class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarClass }}">{{ $initials }}</span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900 truncate">{{ $staffMember->name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $isPartner ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">{{ $isPartner ? __('Partner') : __('Staff') }}</span>
                        <span class="text-xs text-gray-400">{{ $staffMember->monthly_salary && ! $isPartner ? '₹'.number_format($staffMember->monthly_salary, 0).' / month' : __('No salary') }}</span>
                    </div>
                </div>
            </div>

            {{-- Stat cards: Staff --}}
            @unless ($isPartner)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ $month->format('F') }} {{ __('salary') }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">₹{{ number_format($staffMember->monthly_salary ?: 0, 0) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $salaryPaidThisMonth >= ($staffMember->monthly_salary ?: 0) && $staffMember->monthly_salary ? __('paid') : __('not paid yet') }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ __('Advance in :month', ['month' => $month->format('F')]) }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-red-600 mt-2 lg:mt-3">- ₹{{ number_format($advanceThisMonth, 0) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('cut from salary') }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ __('Left to pay for :month', ['month' => $month->format('F')]) }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-brand-700 mt-2 lg:mt-3">₹{{ number_format($leftToPay, 0) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('pay on') }} {{ $nextMonthLabel }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ __('Paid so far (all time)') }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">₹{{ number_format($staffMember->totalSalaryPaid(), 0) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('advance') }} ₹{{ number_format($staffMember->totalUpad(), 0) }}</div>
                    </div>
                </div>
            @else
                {{-- Stat cards: Partner --}}
                <div class="grid grid-cols-2 gap-3 lg:gap-4 lg:max-w-md">
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ __('Withdrawn in :month', ['month' => $month->format('F')]) }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-red-600 mt-2 lg:mt-3">₹{{ number_format($advanceThisMonth, 0) }}</div>
                    </div>
                    <div class="bg-white shadow-sm rounded-xl p-4 lg:p-5 border border-gray-100">
                        <span class="text-xs lg:text-sm text-gray-500">{{ __('Total withdrawals (all time)') }}</span>
                        <div class="text-lg lg:text-2xl font-bold text-gray-900 mt-2 lg:mt-3">₹{{ number_format($staffMember->totalUpad(), 0) }}</div>
                    </div>
                </div>
            @endunless

            <div class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between gap-2 px-4 sm:px-6 pt-4 pb-2 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">{{ __('Payment history') }}</h3>
                        <div class="flex items-center gap-1">
                            @foreach (['all' => __('All'), 'salary' => __('Salary'), 'upad' => __('Advance')] as $tab => $tabLabel)
                                @if ($tab !== 'salary' || ! $isPartner)
                                    <button type="button" @click="activeTab = '{{ $tab }}'"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md"
                                        :class="activeTab === '{{ $tab }}' ? 'bg-brand-50 text-brand-700' : 'text-gray-500 hover:bg-gray-50'">
                                        {{ $tabLabel }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div x-show="activeTab === 'all'" class="divide-y divide-gray-100">
                        @include('staff.partials.payment-rows', ['payments' => $payments, 'staffMember' => $staffMember])
                    </div>
                    <div x-show="activeTab === 'salary'" style="display: none" class="divide-y divide-gray-100">
                        @include('staff.partials.payment-rows', ['payments' => $payments->where('category', 'salary')->values(), 'staffMember' => $staffMember])
                    </div>
                    <div x-show="activeTab === 'upad'" style="display: none" class="divide-y divide-gray-100">
                        @include('staff.partials.payment-rows', ['payments' => $payments->where('category', 'upad')->values(), 'staffMember' => $staffMember])
                    </div>
                </div>

                <div class="hidden lg:block space-y-6 mt-0">
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        @include('staff.partials.payment-form', ['staffMember' => $staffMember, 'monthOptions' => $monthOptions])
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ __('Active') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('Turn off if :name leaves. History stays saved.', ['name' => $staffMember->name]) }}</p>
                        </div>
                        <form method="POST" action="{{ route('staff.toggle-active', $staffMember) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                data-name="{{ $staffMember->name }}"
                                data-active="{{ $staffMember->is_active ? '1' : '0' }}"
                                @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 {{ $staffMember->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $staffMember->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 flex gap-2 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            @unless ($isPartner)
                <button type="button" @click="giveMode = 'upad'; giveModalOpen = true" class="flex-1 px-4 py-2.5 border border-brand-600 text-brand-700 text-sm font-semibold rounded-lg">
                    {{ __('Give advance') }}
                </button>
                <button type="button" @click="giveMode = 'salary'; giveModalOpen = true" class="flex-1 px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                    {{ __('Pay salary') }}
                </button>
            @else
                <button type="button" @click="giveModalOpen = true" class="flex-1 px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                    {{ __('Give withdrawal') }}
                </button>
            @endunless
        </div>

        {{-- Mobile give-money modal --}}
        <div x-show="giveModalOpen" style="display: none" class="lg:hidden fixed inset-0 z-50 flex items-end">
            <div x-show="giveModalOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-gray-900/50" @click="giveModalOpen = false"></div>
            <div x-show="giveModalOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-6"
                class="relative bg-white rounded-t-2xl w-full p-6 max-h-[85vh] overflow-y-auto">
                @include('staff.partials.payment-form', ['staffMember' => $staffMember, 'monthOptions' => $monthOptions])
            </div>
        </div>

        {{-- Mobile active-toggle card --}}
        <div class="lg:hidden max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-white shadow-sm rounded-lg p-4 flex items-center justify-between">
                <div class="min-w-0 pr-3">
                    <p class="text-sm font-medium text-gray-700">{{ __('Active') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('Turn off if :name leaves. History stays saved.', ['name' => $staffMember->name]) }}</p>
                </div>
                <form method="POST" action="{{ route('staff.toggle-active', $staffMember) }}">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $staffMember->name }}"
                        data-active="{{ $staffMember->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 {{ $staffMember->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $staffMember->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        @include('staff.partials.status-confirm-modal')
    </div>
</x-app-layout>
