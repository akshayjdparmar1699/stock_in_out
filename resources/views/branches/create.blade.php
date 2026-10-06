<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Branch') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('branches.store') }}" class="space-y-4" x-data="{ copyItems: {{ old('copy_items') ? 'true' : 'false' }} }">
                    @csrf
                    @include('branches.partials.form', ['branch' => null])

                    @if ($branches->isNotEmpty())
                        <div class="border-t pt-4">
                            <label class="inline-flex items-start gap-2">
                                <input type="checkbox" name="copy_items" value="1" x-model="copyItems"
                                    class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">
                                    {{ __('Copy items from an existing branch') }}
                                    <span class="block text-xs text-gray-400 mt-0.5">
                                        {{ __("Left unchecked, this branch starts with no items — they'll only show up here once you purchase or manually stock them for it.") }}
                                    </span>
                                </span>
                            </label>

                            <div class="mt-3" x-show="copyItems" style="display: none;">
                                <x-input-label for="copy_from_branch_id" :value="__('Copy items from')" />
                                <select id="copy_from_branch_id" name="copy_from_branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('Select branch') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected(old('copy_from_branch_id') == $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('copy_from_branch_id')" class="mt-2" />
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Save Branch') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
