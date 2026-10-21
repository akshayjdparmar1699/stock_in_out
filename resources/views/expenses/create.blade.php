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
                    <span class="text-gray-700 font-medium">{{ __('Add Expense') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Add Expense') }}</h2>
            </div>
            <a href="{{ route('expenses.index') }}" class="hidden lg:inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12"
        x-data="{
            category: '{{ old('category', 'transport') }}',
            amount: {{ (int) old('amount', 0) }},
            mode: '{{ old('mode', 'cash') }}',
            expenseDate: '{{ old('expense_date', now()->format('Y-m-d')) }}',
            note: '{{ old('note') }}',
            categoryLabels: @js(collect($categories)->toArray()),
            get categoryLabel() { return this.categoryLabels[this.category] ?? 'Expense'; },
            addAmount(v) { this.amount = (parseFloat(this.amount) || 0) + v; },
            isToday() { return this.expenseDate === '{{ now()->format('Y-m-d') }}'; },
            isYesterday() { return this.expenseDate === '{{ now()->subDay()->format('Y-m-d') }}'; },
        }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('expenses.store') }}" id="expense-form" class="lg:grid lg:grid-cols-3 lg:gap-6 lg:items-start">
                @csrf
                <input type="hidden" name="category" :value="category">
                <input type="hidden" name="mode" :value="mode">

                <div class="lg:col-span-2 space-y-4">
                    {{-- What was it for --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('What was it for?') }}</h3>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                            @foreach ($categories as $value => $label)
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

                    {{-- Amount / paid by / date --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <div>
                            <x-input-label for="amount">{{ __('Amount') }} <span class="text-red-500">*</span></x-input-label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">&#8377;</span>
                                <input id="amount" name="amount" type="number" step="0.01" min="0.01" x-model.number="amount" required
                                    class="no-spinner w-full pl-8 border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-lg font-semibold">
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" @click="addAmount(100)" class="px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">+ &#8377;100</button>
                                <button type="button" @click="addAmount(500)" class="px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">+ &#8377;500</button>
                                <button type="button" @click="addAmount(1000)" class="px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">+ &#8377;1,000</button>
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
                            <x-input-label for="expense_date">{{ __('Date') }}</x-input-label>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <button type="button" @click="expenseDate = '{{ now()->format('Y-m-d') }}'"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border-2" :class="isToday() ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    {{ __('Today') }}
                                </button>
                                <button type="button" @click="expenseDate = '{{ now()->subDay()->format('Y-m-d') }}'"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border-2" :class="isYesterday() ? 'border-brand-600 text-brand-700 bg-brand-50' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    {{ __('Yesterday') }}
                                </button>
                                <input id="expense_date" name="expense_date" type="date" x-model="expenseDate" max="{{ now()->format('Y-m-d') }}" required
                                    class="border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                            </div>
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Person + note --}}
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 space-y-4">
                        <div>
                            <x-input-label for="staff_member_id">{{ __('For a staff or partner?') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <select id="staff_member_id" name="staff_member_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                <option value="">{{ __('Not for a person') }}</option>
                                @foreach ($staffOptions as $member)
                                    <option value="{{ $member->id }}" @selected((string) old('staff_member_id') === (string) $member->id)>
                                        {{ $member->name }} ({{ $member->isPartner() ? __('Partner') : __('Staff') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Pick a person only if this money was given to them.') }}</p>
                            <x-input-error :messages="$errors->get('staff_member_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="note">{{ __('Note') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional') }})</span></x-input-label>
                            <x-text-input id="note" name="note" type="text" class="mt-1 block w-full" x-model="note" placeholder="{{ __('e.g. Potato transport, Rajula to Kodinar') }}" />
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                            @if ($recentNotes->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-xs text-gray-400">{{ __('Recent:') }}</span>
                                    @foreach ($recentNotes as $recent)
                                        <button type="button" @click="note = {{ \Illuminate\Support\Js::from($recent) }}"
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-brand-50 text-brand-700 hover:bg-brand-100">
                                            {{ $recent }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="hidden lg:block lg:sticky lg:top-20">
                    <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ __('You are adding') }}</p>
                            <p class="font-semibold text-gray-900 text-lg mt-1" x-text="categoryLabel"></p>
                        </div>

                        <button type="submit" :disabled="!amount || amount <= 0"
                            class="w-full px-4 py-3 bg-brand-700 text-white text-sm font-semibold rounded-lg hover:bg-brand-800 disabled:opacity-40 disabled:cursor-not-allowed">
                            {{ __('Save Expense') }}
                        </button>
                        <button type="submit" name="add_another" value="1" :disabled="!amount || amount <= 0"
                            class="w-full px-4 py-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            {{ __('Save and add another') }}
                        </button>

                        @if ($todaysExpenses->isNotEmpty())
                            <div class="pt-4 border-t border-gray-100">
                                <div class="flex justify-between items-center text-sm mb-2">
                                    <span class="text-gray-500">{{ __('Today so far') }}</span>
                                    <span class="font-semibold text-red-600">&#8377;{{ number_format($todaysExpenses->sum('amount'), 0) }}</span>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach ($todaysExpenses->take(5) as $today)
                                        <div class="flex justify-between items-center text-sm text-gray-600">
                                            <span class="truncate pr-2">{{ $today->note ?: $today->categoryLabel() }}</span>
                                            <span class="shrink-0">&#8377;{{ number_format($today->amount, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="expense-form" :disabled="!amount || amount <= 0"
                class="w-full px-4 py-3 bg-brand-700 text-white text-sm font-semibold rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">
                {{ __('Save Expense') }}
            </button>
        </div>
    </div>
</x-app-layout>
