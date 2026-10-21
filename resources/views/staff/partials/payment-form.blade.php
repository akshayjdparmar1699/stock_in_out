@php
    $monthsJson = \Illuminate\Support\Js::from($monthOptions);
@endphp
<div x-data="{
        category: {{ $staffMember->isPartner() ? "'upad'" : "(typeof giveMode !== 'undefined' ? giveMode : 'salary')" }},
        mode: 'cash',
        months: {{ $monthsJson }},
        selectedMonth: '{{ $monthOptions->first()['key'] ?? now()->format('Y-m-01') }}',
        get monthData() { return this.months.find(m => m.key === this.selectedMonth) || {}; },
        amount: {{ $monthOptions->first()['suggested'] ?? 0 }},
    }"
    x-init="$watch('selectedMonth', () => { amount = monthData.suggested })"
    @if (! $staffMember->isPartner())
        x-effect="if (typeof giveMode !== 'undefined') { category = giveMode; amount = giveMode === 'salary' ? monthData.suggested : 0 }"
    @endif>
    <h3 class="font-semibold text-gray-800 mb-4">{{ __('Give money') }}</h3>

    <form method="POST" action="{{ route('staff.payments.store', $staffMember) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="mode" :value="mode">
        <input type="hidden" name="category" :value="category">
        <input type="hidden" name="for_month" :value="category === 'salary' ? selectedMonth : ''">

        @unless ($staffMember->isPartner())
            <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden text-sm w-full">
                <button type="button" @click="category = 'salary'; amount = monthData.suggested" class="flex-1 px-3 py-1.5 font-medium" :class="category === 'salary' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                    {{ __('Salary') }}
                </button>
                <button type="button" @click="category = 'upad'; amount = 0" class="flex-1 px-3 py-1.5 font-medium border-l border-gray-200" :class="category === 'upad' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600'">
                    {{ __('Advance (upad)') }}
                </button>
            </div>
        @endunless

        <div x-show="category === 'salary'">
            <x-input-label for="for_month_{{ $staffMember->id }}" :value="__('Salary for month')" />
            <select id="for_month_{{ $staffMember->id }}" x-model="selectedMonth" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                <template x-for="m in months" :key="m.key">
                    <option :value="m.key" x-text="m.label"></option>
                </template>
            </select>
        </div>

        <div>
            <x-input-label for="amount_{{ $staffMember->id }}" :value="__('Amount')" />
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                <input id="amount_{{ $staffMember->id }}" name="amount" type="number" step="0.01" min="0.01" x-model.number="amount"
                    class="w-full pl-7 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm" required>
            </div>
            <p class="text-xs text-gray-400 mt-1" x-show="category === 'salary' && monthData.advance > 0">
                {{ __('₹') }}<span x-text="({{ (float) $staffMember->monthly_salary }}).toFixed(0)"></span> {{ __('salary minus') }}
                ₹<span x-text="(monthData.advance || 0).toFixed(0)"></span> {{ __('advance') }} =
                ₹<span x-text="(monthData.suggested || 0).toFixed(0)"></span>
            </p>
        </div>

        <div class="grid grid-cols-3 gap-2">
            @foreach (['cash' => __('Cash'), 'upi' => __('UPI'), 'bank' => __('Bank')] as $value => $label)
                <button type="button" @click="mode = '{{ $value }}'"
                    class="px-3 py-2 text-sm font-medium rounded-md border"
                    :class="mode === '{{ $value }}' ? 'bg-brand-50 border-brand-500 text-brand-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <button type="submit" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm rounded-lg py-2.5"
            x-text="category === 'salary' ? '{{ __('Pay salary') }}' : '{{ $staffMember->isPartner() ? __('Give withdrawal') : __('Give advance') }}'">
        </button>

        <p class="text-xs text-gray-400 text-center">{{ __('Also saved in Expenses automatically.') }}</p>
    </form>
</div>
