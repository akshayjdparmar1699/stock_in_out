<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Branches') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __("Your shops and godowns. Each branch has its own stock, bills and bank details.") }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <a href="{{ route('branches.create') }}" class="hidden lg:inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('+ Add Branch') }}
                </a>
            </div>
        </div>
    </x-slot>

    @php $currentBranchId = \App\Services\BranchContext::id(); @endphp

    <div class="py-6 sm:py-12 pb-28 lg:pb-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ($branches as $branch)
                    @php
                        $missingContact = ! $branch->phone || ! $branch->address;
                        $hasCustomBank = $branch->bank_name || $branch->account_number || $branch->account_holder_name || $branch->ifsc_code || $branch->qr_code_path;
                        $userLabel = $branch->users_count === 0
                            ? __('Only admin')
                            : trans_choice('{1} 1 staff user|[2,*] :count staff users', $branch->users_count, ['count' => $branch->users_count]);
                    @endphp
                    <div class="bg-white rounded-2xl border {{ $branch->id === $currentBranchId ? 'border-brand-300' : 'border-gray-100' }} shadow-sm p-5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-11 h-11 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">{{ $branch->name }}</div>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ __('Code') }} {{ $branch->code }}</span>
                                    @if ($branch->id === $currentBranchId)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">{{ __('YOU ARE HERE') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                            <div>
                                <div class="text-xs text-gray-400">{{ __('Phone') }}</div>
                                <div class="{{ $branch->phone ? 'text-gray-700' : 'text-amber-600 font-medium' }}">{{ $branch->phone ?: __('Not added') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">{{ __('Address') }}</div>
                                <div class="{{ $branch->address ? 'text-gray-700' : 'text-amber-600 font-medium' }} truncate">{{ $branch->address ?: __('Not added') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">{{ __('Users with login') }}</div>
                                <div class="text-gray-700 font-medium">{{ $userLabel }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">{{ __('Bank and QR on bills') }}</div>
                                <div class="text-gray-700 font-medium">
                                    {{ $hasCustomBank ? $branch->paymentBankName() : __('Default (:bank)', ['bank' => $branch->paymentBankName()]) }}
                                </div>
                            </div>
                        </div>

                        @if ($missingContact)
                            <div class="mt-4 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs">
                                @if (! $branch->phone && ! $branch->address)
                                    {{ __("Phone and address are missing. They print on this branch's bills.") }}
                                @elseif (! $branch->phone)
                                    {{ __("Phone is missing. It prints on this branch's bills.") }}
                                @else
                                    {{ __("Address is missing. It prints on this branch's bills.") }}
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('branches.edit', $branch) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                                {{ __('Edit details') }}
                            </a>
                            <a href="{{ route('users.index') }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                                {{ __('Manage users') }}
                            </a>
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('branches.create') }}" class="flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/40 hover:bg-brand-50 p-8 text-center min-h-[200px]">
                    <span class="w-11 h-11 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </span>
                    <span class="font-semibold text-brand-700">{{ __('Add another branch') }}</span>
                    <span class="text-xs text-gray-400">{{ __('New shop or godown') }}</span>
                </a>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <a href="{{ route('branches.create') }}" class="block w-full text-center px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('+ Add Branch') }}
            </a>
        </div>
    </div>
</x-app-layout>
