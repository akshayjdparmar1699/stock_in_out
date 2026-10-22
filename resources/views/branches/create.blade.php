<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('branches.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('branches.index') }}" class="hover:text-gray-600">{{ __('Branches') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Add Branch') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Add Branch') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <a href="{{ route('branches.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" form="branch-form" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('Save Branch') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('branches.store') }}" enctype="multipart/form-data" id="branch-form"
                x-data="{ bankMode: '{{ old('bank_mode', 'default') }}', copyItems: {{ old('copy_items') ? 'true' : 'false' }} }">
                @csrf

                @include('branches.partials.form', ['branch' => null])

                @if ($branches->isNotEmpty())
                    <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6 mt-4">
                        <div class="flex items-center gap-2.5 mb-4">
                            <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 text-sm font-semibold flex items-center justify-center shrink-0">3</span>
                            <h3 class="font-semibold text-gray-900">{{ __('Items in this branch') }}</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" @click="copyItems = true"
                                class="text-left p-4 rounded-xl border-2"
                                :class="copyItems ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="font-semibold text-gray-900">{{ __('Copy items from a branch') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ __('Same item list, ready for billing') }}</div>
                            </button>
                            <button type="button" @click="copyItems = false"
                                class="text-left p-4 rounded-xl border-2"
                                :class="!copyItems ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
                                <div class="font-semibold text-gray-900">{{ __('Start with no items') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ __('Items appear when you buy or add stock') }}</div>
                            </button>
                        </div>

                        <input type="hidden" name="copy_items" :value="copyItems ? '1' : '0'">

                        <div class="mt-4" x-show="copyItems" style="display: none;">
                            <x-input-label for="copy_from_branch_id" :value="__('Copy items from')" />
                            <select id="copy_from_branch_id" name="copy_from_branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">{{ __('Select branch') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('copy_from_branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-2">{{ __('Only item names, units and prices are copied. Stock starts at 0.') }}</p>
                            <x-input-error :messages="$errors->get('copy_from_branch_id')" class="mt-2" />
                        </div>
                    </div>
                @endif
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="branch-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Save Branch') }}
            </button>
        </div>
    </div>
</x-app-layout>
