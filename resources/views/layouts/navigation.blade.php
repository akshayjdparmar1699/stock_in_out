@php
    $sidebarSubscription = Auth::user()->isSuperAdmin() ? null : Auth::user()->company?->subscription?->load('plan');
@endphp
<aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-white border-r border-gray-200">
    <a href="{{ Auth::user()->isSuperAdmin() ? route('companies.index') : route('dashboard') }}" class="h-16 shrink-0 flex items-center justify-between gap-3 px-6 border-b border-gray-100">
        <span class="flex items-center gap-3 min-w-0">
            <x-application-logo class="w-8 h-8 text-brand-600 shrink-0" />
            <span class="font-semibold text-gray-800 truncate">{{ config('app.name') }}</span>
        </span>
        @if ($sidebarSubscription?->plan)
            <span @class([
                'shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold',
                'bg-red-50 text-red-600' => ! $sidebarSubscription->isActive(),
                'bg-brand-50 text-brand-700' => $sidebarSubscription->isActive(),
            ])>{{ $sidebarSubscription->plan->name }}</span>
        @endif
    </a>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        @include('layouts.partials.sidebar-links')
    </nav>

    <div class="px-3 py-4 border-t border-gray-100">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('profile.edit') ? 'bg-brand-50' : '' }}">
            <span class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold shrink-0 overflow-hidden">
                @if (Auth::user()->avatarUrl())
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @endif
            </span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</span>
                <span class="block text-xs text-gray-400 truncate">{{ __('My profile') }}</span>
            </span>
        </a>
    </div>
</aside>
