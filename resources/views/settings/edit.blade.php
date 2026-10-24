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
            <div class="lg:grid lg:grid-cols-[260px_1fr] lg:gap-6 lg:items-start">

                {{-- Section nav — only Invoice & templates is wired up for now --}}
                <nav class="hidden lg:block bg-white rounded-2xl border border-gray-100 shadow-sm p-2 sticky top-24 space-y-0.5">
                    <div class="px-3 py-2 rounded-lg text-sm font-medium text-gray-400">{{ __('Business profile') }}</div>
                    <div class="px-3 py-2 rounded-lg text-sm font-semibold bg-brand-50 text-brand-700">{{ __('Invoice & templates') }}</div>
                    @foreach ([
                        __('GST & tax'), __('Payment details'), __('Items & stock'), __('Customers & credit'),
                        __('WhatsApp & messages'), __('Notifications'), __('Branches'), __('Users & roles'),
                        __('Subscription'), __('Data & backup'),
                    ] as $label)
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium text-gray-400">
                            <span>{{ $label }}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-400 rounded-full px-1.5 py-0.5">{{ __('Soon') }}</span>
                        </div>
                    @endforeach
                </nav>

                <div class="space-y-4" x-data="{
                    selected: {{ \Illuminate\Support\Js::from($selectedFormat ?? '') }},
                    get previewUrl() {
                        return {{ \Illuminate\Support\Js::from(route('settings.invoice-format.preview')) }} + '?format=' + encodeURIComponent(this.selected);
                    }
                }">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6">
                        <div class="flex items-start justify-between gap-3 mb-1">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('Invoice format') }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ __('Pick how your bill looks when you print it or share it with customers.') }} @if ($branch) &middot; <span class="font-medium text-gray-600">{{ $branch->name }}</span> @endif</p>
                            </div>
                            <a :href="previewUrl" target="_blank" class="shrink-0 text-sm font-semibold text-brand-700 hover:underline whitespace-nowrap">{{ __('Preview full bill') }}</a>
                        </div>

                        <form method="POST" action="{{ route('settings.invoice-format') }}" class="mt-4">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <button type="button" @click="selected = ''"
                                    class="text-left p-4 rounded-xl border-2"
                                    :class="selected === '' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold text-gray-900">{{ __('Original') }}</div>
                                        <span class="text-[10px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-700 rounded-full px-1.5 py-0.5">{{ __('Default') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ __("Your bill's current design — nothing changes unless you pick another.") }}</div>
                                </button>

                                @foreach ($formats as $key => $format)
                                    <button type="button" @click="selected = '{{ $key }}'"
                                        class="text-left p-4 rounded-xl border-2"
                                        :class="selected === '{{ $key }}' ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="font-semibold text-gray-900">{{ $format['name'] }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $format['description'] }}</div>
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

                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5 text-sm text-gray-400">
                        {{ __('Branding, invoice numbering, what shows on the bill, and the rest of these settings are coming soon.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
