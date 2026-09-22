<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Staff / Partner') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('staff.store') }}" class="space-y-4"
                    x-data="{ type: '{{ old('type', 'staff') }}' }"
                    @change="if ($event.target.name === 'type') { $refs.branchBoxes.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = (type === 'partner')) }">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label :value="__('Type')" />
                        <div class="mt-2 flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="staff" x-model="type" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Staff (employee)') }}</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="type" value="partner" x-model="type" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ __('Partner') }}</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Partners are shared across all branches by default. Staff belong to the branch you\'re currently on, unless you pick branches below.') }}</p>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="monthly_salary" :value="__('Monthly Salary (optional)')" />
                        <x-text-input id="monthly_salary" name="monthly_salary" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_salary')" placeholder="0.00" />
                        <x-input-error :messages="$errors->get('monthly_salary')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone (optional)')" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    @if (Auth::user()->isAdmin())
                        <div>
                            <x-input-label :value="__('Applies to Branch(es)')" />
                            <div class="mt-2 space-y-2" x-ref="branchBoxes">
                                @foreach ($branches as $branch)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(collect(old('branch_ids', old('type') === 'partner' ? $branches->pluck('id')->all() : [\App\Services\BranchContext::id()]))->contains($branch->id))>
                                        <span class="text-sm text-gray-700">{{ $branch->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Switching type above auto-selects the usual branches for that type, uncheck/check to override.') }}</p>
                            <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
