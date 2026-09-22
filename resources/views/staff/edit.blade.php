<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Staff / Partner') }}</h2>
    </x-slot>

    <div class="py-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('staff.update', $staffMember) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $staffMember->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label :value="__('Type')" />
                        <div class="mt-2 flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="staff" class="text-indigo-600 focus:ring-indigo-500" @checked(old('type', $staffMember->type) === 'staff')>
                                <span class="text-sm text-gray-700">{{ __('Staff (employee)') }}</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="partner" class="text-indigo-600 focus:ring-indigo-500" @checked(old('type', $staffMember->type) === 'partner')>
                                <span class="text-sm text-gray-700">{{ __('Partner') }}</span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monthly_salary" :value="__('Monthly Salary (optional)')" />
                        <x-text-input id="monthly_salary" name="monthly_salary" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_salary', $staffMember->monthly_salary)" placeholder="0.00" />
                        <x-input-error :messages="$errors->get('monthly_salary')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone (optional)')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $staffMember->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        @php $currentBranchIds = old('branch_ids', $staffMember->branches->pluck('id')->all()); @endphp
                        <div>
                            <x-input-label :value="__('Applies to Branch(es)')" />
                            <div class="mt-2 space-y-2">
                                @foreach ($branches as $branch)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(collect($currentBranchIds)->contains($branch->id))>
                                        <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                        </div>
                    @else
                        <div>
                            <x-input-label :value="__('Branch(es)')" />
                            <p class="text-sm text-gray-600 mt-1">{{ $staffMember->branches->pluck('name')->join(', ') }}</p>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 grid grid-cols-2 gap-4">
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

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                    <p class="text-sm font-medium {{ $staffMember->is_active ? 'text-green-600' : 'text-gray-500' }}">
                        {{ $staffMember->is_active ? __('Active') : __('Inactive') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('staff.toggle-active', $staffMember) }}">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $staffMember->name }}"
                        data-active="{{ $staffMember->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $staffMember->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $staffMember->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        @include('staff.partials.status-confirm-modal')
    </div>
</x-app-layout>
