<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settings') }}</h2>
                <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Set up how your bills, stock and team work') }}</p>
            </div>
            <div class="lg:hidden">
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-[220px_1fr] lg:gap-6 lg:items-start">

                <nav class="hidden lg:block bg-white rounded-xl border border-gray-100 shadow-sm p-2 sticky top-24">
                    <span class="block px-3 py-2 rounded-lg text-sm font-semibold bg-brand-50 text-brand-700">{{ __('Invoice format') }}</span>
                </nav>

                <div x-data="{
                    selected: {{ \Illuminate\Support\Js::from($selectedFormat) }},
                    get previewUrl() {
                        return {{ \Illuminate\Support\Js::from(route('settings.invoice-format.preview')) }} + '?format=' + encodeURIComponent(this.selected);
                    }
                }">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('Invoice format') }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ __('Pick how your bill looks when you print it or share it with customers.') }} @if ($branch) &middot; <span class="font-medium text-gray-600">{{ $branch->name }}</span> @endif</p>
                            </div>
                            <a :href="previewUrl" target="_blank" class="shrink-0 text-sm font-semibold text-brand-700 hover:underline whitespace-nowrap">{{ __('Preview full bill') }}</a>
                        </div>

                        <form method="POST" action="{{ route('settings.invoice-format') }}">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach ($formats as $key => $format)
                                    <button type="button" @click="selected = '{{ $key }}'" class="text-left rounded-xl border-2 overflow-hidden relative"
                                        :class="selected === '{{ $key }}' ? 'border-brand-500' : 'border-gray-200 hover:border-gray-300'">
                                        <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center" x-show="selected === '{{ $key }}'" style="display: none;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </div>
                                        <div class="aspect-[480/677] bg-gray-50 overflow-hidden">
                                            <img src="{{ asset($format['thumbnail']) }}" alt="{{ $format['name'] }}" class="w-full h-full object-cover object-top">
                                        </div>
                                        <div class="p-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-semibold text-gray-900 text-sm">{{ $format['name'] }}</span>
                                                @if ($key === config('invoice_formats.default'))
                                                    <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-700 rounded-full px-1.5 py-0.5">{{ __('Default') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $format['description'] }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <input type="hidden" name="invoice_format" :value="selected">

                            <div class="flex justify-end mt-5">
                                <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                                    {{ __('Save changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
