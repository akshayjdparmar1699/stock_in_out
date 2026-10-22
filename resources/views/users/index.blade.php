<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('People who can log in to the app') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    @include('layouts.partials.notification-bell')
                </div>
                <a href="{{ route('users.create') }}" class="hidden lg:inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('+ Add User') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ confirmOpen: false, confirmForm: null, confirmName: '', confirmActive: false, mobileView: 'cards' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Role explainer (desktop: two cards, mobile: one combined banner) --}}
            <div class="hidden lg:grid lg:grid-cols-2 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-4 border border-gray-100">
                    <span class="font-semibold text-gray-900">{{ __('Admin') }}</span>
                    <span class="text-sm text-gray-500">{{ __('Sees all branches and can manage everything, including users and branches.') }}</span>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4 border border-gray-100">
                    <span class="font-semibold text-gray-900">{{ __('Staff') }}</span>
                    <span class="text-sm text-gray-500">{{ __('Works in one branch only. Good for counter staff who make bills.') }}</span>
                </div>
            </div>
            <div class="lg:hidden bg-white shadow-sm rounded-xl p-4 border border-gray-100 text-sm space-x-1">
                <span><span class="font-semibold text-brand-700">{{ __('Admin:') }}</span> {{ __('all branches, can manage everything.') }}</span>
                <span class="block mt-1"><span class="font-semibold text-blue-600">{{ __('Staff:') }}</span> {{ __('works in one branch only.') }}</span>
            </div>

            <div class="hidden lg:flex items-center justify-between gap-3">
                <span class="text-sm font-medium text-gray-700">{{ $users->total() }} {{ __('users') }}</span>
                <form method="GET" class="flex gap-2">
                    <x-text-input name="q" type="text" class="w-full sm:w-72" placeholder="{{ __('Search name or email') }}" :value="request('q')" />
                    <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
                </form>
            </div>
            <form method="GET" class="lg:hidden flex gap-2">
                <x-text-input name="q" type="text" class="w-full" placeholder="{{ __('Search name or email') }}" :value="request('q')" />
                <x-secondary-button type="submit">{{ __('Search') }}</x-secondary-button>
            </form>

            {{-- Mobile card/table view toggle --}}
            <div class="flex lg:hidden justify-end">
                <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden shrink-0">
                    <button type="button" @click="mobileView = 'cards'" :class="mobileView === 'cards' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium" aria-label="{{ __('Card view') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        {{ __('Cards') }}
                    </button>
                    <button type="button" @click="mobileView = 'table'" :class="mobileView === 'table' ? 'bg-brand-600 text-white' : 'bg-white text-gray-500'" class="flex items-center gap-1.5 px-3 h-9 text-xs font-medium border-l border-gray-200" aria-label="{{ __('Table view') }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        {{ __('Table') }}
                    </button>
                </div>
            </div>

            <div x-data="listTable()">
                <div x-ref="content" :class="loading && 'opacity-50'">
                    @include('users.partials.table')
                </div>
            </div>
        </div>

        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <a href="{{ route('users.create') }}" class="block w-full text-center px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('+ Add User') }}
            </a>
        </div>

        @include('users.partials.status-confirm-modal')
    </div>
</x-app-layout>
