@php
    $tabClass = fn (bool $active) => $active
        ? 'flex flex-col items-center justify-center gap-0.5 flex-1 py-2 text-brand-600'
        : 'flex flex-col items-center justify-center gap-0.5 flex-1 py-2 text-gray-500';
@endphp

{{-- The super admin's sidebar only ever has two destinations (see
sidebar-links.blade.php) — this mirrors that instead of reusing the
per-company mobile-bottom-nav/mobile-more-sheet partials, which are built
entirely around per-company modules a super admin never sees. --}}
<nav class="lg:hidden print:hidden fixed inset-x-0 bottom-0 z-40 bg-white border-t border-gray-200 flex transform-gpu"
    style="padding-bottom: env(safe-area-inset-bottom);">
    <a href="{{ route('companies.index') }}" class="{{ $tabClass(request()->routeIs('companies.*')) }}">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
        <span class="text-[11px] font-medium">{{ __('Companies') }}</span>
    </a>

    <a href="{{ route('leads.index') }}" class="{{ $tabClass(request()->routeIs('leads.*')) }}">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.584-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.124-2.995-2.708-3.228A48.35 48.35 0 0012 3c-2.392 0-4.744.175-7.042.513C3.374 3.746 2.25 5.14 2.25 6.741v6.018z" />
        </svg>
        <span class="text-[11px] font-medium">{{ __('Leads') }}</span>
    </a>
</nav>
