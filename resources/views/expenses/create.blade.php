<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Expense') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="category" :value="__('Category')" />
                        <select id="category" name="category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="staff_member_id" :value="__('Staff / Partner (optional)')" />
                        <select id="staff_member_id" name="staff_member_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Not tied to a person') }}</option>
                            @foreach ($staffOptions as $member)
                                <option value="{{ $member->id }}" @selected((string) old('staff_member_id') === (string) $member->id)>
                                    {{ $member->name }} ({{ $member->isPartner() ? __('Partner') : __('Staff') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Required for Salary or Advance so it shows up against that person\'s total.') }}</p>
                        <x-input-error :messages="$errors->get('staff_member_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" :value="__('Amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="expense_date" :value="__('Date')" />
                        <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', now()->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="note" :value="__('Note (optional)')" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 block w-full" :value="old('note')" placeholder="{{ __('e.g. Petrol for delivery bike') }}" />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Save Expense') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
