<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.show', $staffMember) }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('staff.index') }}" class="hover:text-gray-600">{{ __('Staff & Partners') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit') }} {{ $staffMember->name }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit person') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2">
                <a href="{{ route('staff.show', $staffMember) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" form="staff-form" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ type: '{{ old('type', $staffMember->type) }}' }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="POST" action="{{ route('staff.update', $staffMember) }}" id="staff-form" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Who is this?') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer" :class="type === 'staff' ? 'border-brand-600 bg-brand-50/40' : 'border-gray-200'">
                            <input type="radio" name="type" value="staff" x-model="type" class="sr-only">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0" :class="type === 'staff' ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500'">S</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ __('Staff (employee)') }}</span>
                                <span class="block text-xs text-gray-400 mt-0.5">{{ __('Works for you, gets salary or advance') }}</span>
                            </span>
                        </label>
                        <label class="relative flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer" :class="type === 'partner' ? 'border-brand-600 bg-brand-50/40' : 'border-gray-200'">
                            <input type="radio" name="type" value="partner" x-model="type" class="sr-only">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0" :class="type === 'partner' ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500'">P</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ __('Partner (owner)') }}</span>
                                <span class="block text-xs text-gray-400 mt-0.5">{{ __('Shares the business, no salary') }}</span>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Details') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name">{{ __('Name') }} <span class="text-red-500">*</span></x-input-label>
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $staffMember->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone">{{ __('Phone') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $staffMember->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4" x-show="type === 'staff'">
                        <x-input-label for="monthly_salary">{{ __('Monthly salary') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <div class="relative mt-1 max-w-xs">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                            <input id="monthly_salary" name="monthly_salary" type="number" step="0.01" min="0" class="w-full pl-7 pr-16 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" value="{{ old('monthly_salary', $staffMember->monthly_salary) }}">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ __('/ month') }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Used to show how much salary is left to pay each month') }}</p>
                        <x-input-error :messages="$errors->get('monthly_salary')" class="mt-2" />
                    </div>
                </div>

                @if (Auth::user()->isAdmin())
                    @php $currentBranchIds = old('branch_ids', $staffMember->branches->pluck('id')->all()); @endphp
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ __('Works at') }}</h3>
                        <p class="text-xs text-gray-400 mb-3">{{ __('Tick every branch this person works at.') }}</p>
                        <div class="space-y-2">
                            @foreach ($branches as $branch)
                                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40">
                                    <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                        class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500"
                                        @checked(collect($currentBranchIds)->contains($branch->id))>
                                    <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                    </div>
                @else
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ __('Works at') }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $staffMember->branches->pluck('name')->join(', ') }}</p>
                    </div>
                @endif
            </form>

            <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Total Advance (all time)') }}</p>
                    <p class="text-2xl font-semibold {{ $staffMember->totalUpad() > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        ₹{{ number_format($staffMember->totalUpad(), 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Total Salary Paid (all time)') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">₹{{ number_format($staffMember->totalSalaryPaid(), 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Mobile sticky save bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="staff-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</x-app-layout>
