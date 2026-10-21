<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('expenses.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('expenses.index') }}" class="hover:text-gray-600">{{ __('Expenses') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Edit Expense') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Expense') }}</h2>
            </div>
            <a href="{{ route('expenses.index') }}" class="hidden lg:inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ category: {{ \Illuminate\Support\Js::from(old('category', $expense->category)) }}, mode: {{ \Illuminate\Support\Js::from(old('mode', $expense->mode ?? 'cash')) }} }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="POST" action="{{ route('expenses.update', $expense) }}" id="expense-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="category" :value="category">
                <input type="hidden" name="mode" :value="mode">

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 mb-4">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('What was it for?') }}</h3>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                        @foreach (\App\Models\Expense::primaryCategories() as $value => $label)
                            @php $meta = \App\Models\Expense::categoryMeta()[$value] ?? ['icon' => 'other', 'color' => 'bg-gray-100 text-gray-600']; @endphp
                            <button type="button" @click="category = '{{ $value }}'"
                                class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 text-center"
                                :class="category === '{{ $value }}' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
                                <span class="w-9 h-9 rounded-full flex items-center justify-center {{ $meta['color'] }}">
                                    @include('expenses.partials.category-icon', ['icon' => $meta['icon']])
                                </span>
                                <span class="text-xs font-medium text-gray-700">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                </div>

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">&#8377;</span>
                            <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" required
                                class="no-spinner w-full pl-8 border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label>{{ __('Paid by') }}</x-input-label>
                        <div class="grid grid-cols-3 gap-2 mt-1">
                            @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                                <button type="button" @click="mode = '{{ $value }}'"
                                    class="px-2 py-2.5 text-sm font-medium rounded-lg border-2"
                                    :class="mode === '{{ $value }}' ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <x-input-label for="expense_date" :value="__('Date')" />
                        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', $expense->expense_date->format('Y-m-d'))" max="{{ now()->format('Y-m-d') }}" required />
                        <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="staff_member_id">{{ __('For a staff or partner?') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <select id="staff_member_id" name="staff_member_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                            <option value="">{{ __('Not for a person') }}</option>
                            @foreach ($staffOptions as $member)
                                <option value="{{ $member->id }}" @selected((string) old('staff_member_id', $expense->staff_member_id) === (string) $member->id)>
                                    {{ $member->name }} ({{ $member->isPartner() ? __('Partner') : __('Staff') }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('staff_member_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="note">{{ __('Note') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                        <x-text-input id="note" name="note" type="text" class="mt-1 block w-full" :value="old('note', $expense->note)" />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>
                </div>

                <div class="hidden lg:flex justify-end gap-2 mt-4">
                    <a href="{{ route('expenses.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Update Expense') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="expense-form" class="w-full px-4 py-3 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Update Expense') }}
            </button>
        </div>
    </div>
</x-app-layout>
