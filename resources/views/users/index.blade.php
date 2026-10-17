<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users') }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <a href="{{ route('register') }}" class="hidden lg:inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('+ Add User') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <form method="GET" class="flex gap-2">
                <x-text-input name="q" type="text" class="w-full sm:w-80" placeholder="{{ __('Search by name or email') }}" :value="request('q')" />
                <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
            </form>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('users.partials.table')
                </div>
            </div>
        </div>

        @include('users.partials.status-confirm-modal')
    </div>
</x-app-layout>
