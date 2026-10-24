<x-app-layout>
    <x-slot name="header">
        <div class="hidden lg:block">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Profile') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Manage your account and alerts') }}</p>
        </div>
    </x-slot>

    @php
        $initials = strtoupper(substr(trim($user->name), 0, 2));
    @endphp

    <!-- Mobile hero header (purple) — desktop uses the banner card below instead -->
    <div class="lg:hidden bg-gradient-to-br from-brand-600 to-brand-800 text-white px-5 pt-5 pb-16 rounded-b-[2rem] relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/5"></div>
        <div class="absolute right-8 bottom-0 w-24 h-24 rounded-full bg-white/5"></div>

        <div class="relative flex items-center justify-between">
            <a href="{{ url()->previous() }}" aria-label="{{ __('Back') }}" class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <h1 class="font-semibold text-base">{{ __('Profile') }}</h1>
            <div class="flex items-center gap-1 shrink-0">
                @if (Auth::user()->isAdmin())
                    <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center shrink-0 [&>a]:!text-white [&>a]:!p-0 [&>a>svg]:w-6 [&>a>svg]:h-6">
                        @include('layouts.partials.settings-icon-mobile')
                    </div>
                @endif
                <div class="relative w-10 h-10 rounded-full bg-white/15 flex items-center justify-center shrink-0 [&>div>button]:!text-white [&>div>button]:!p-0 [&>div>button>svg]:w-6 [&>div>button>svg]:h-6">
                    @include('layouts.partials.notification-bell')
                </div>
            </div>
        </div>

        <div class="relative flex flex-col items-center mt-5 text-center">
            @include('profile.partials.avatar', ['size' => 'w-20 h-20', 'textSize' => 'text-2xl'])
            <p class="font-semibold text-lg mt-4">{{ $user->name }}</p>
            <p class="text-sm text-white/70">{{ $user->email }}</p>
            <div class="flex items-center gap-2 mt-3 flex-wrap justify-center">
                @if ($user->isAdmin())
                    <span class="px-2.5 py-1 rounded-full bg-[#FFB020] text-white text-xs font-semibold">{{ __('Admin') }}</span>
                @elseif ($user->isSuperAdmin())
                    <span class="px-2.5 py-1 rounded-full bg-[#FFB020] text-white text-xs font-semibold">{{ __('Super Admin') }}</span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-semibold">{{ __('Staff') }}</span>
                @endif
                @if ($subscription?->plan)
                    <span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-medium">{{ $subscription->plan->name }} {{ __('plan') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="lg:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-6 space-y-6 -mt-10 lg:mt-0">

            <!-- Desktop banner card -->
            <div class="hidden lg:block bg-gradient-to-br from-brand-600 to-brand-800 text-white rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute -right-10 -top-16 w-56 h-56 rounded-full bg-white/5"></div>
                <div class="absolute right-16 -bottom-16 w-40 h-40 rounded-full bg-white/5"></div>

                <div class="relative flex items-center gap-5">
                    @include('profile.partials.avatar', ['size' => 'w-20 h-20', 'textSize' => 'text-2xl'])
                    <div class="min-w-0">
                        <p class="font-bold text-2xl truncate">{{ $user->name }}</p>
                        <p class="text-white/70 truncate">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-2.5">
                            @if ($user->isAdmin())
                                <span class="px-2.5 py-1 rounded-full bg-[#FFB020] text-white text-xs font-semibold">{{ __('Admin') }}</span>
                            @elseif ($user->isSuperAdmin())
                                <span class="px-2.5 py-1 rounded-full bg-[#FFB020] text-white text-xs font-semibold">{{ __('Super Admin') }}</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-semibold">{{ __('Staff') }}</span>
                            @endif
                            @unless ($user->isSuperAdmin())
                                <span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-medium">{{ \App\Services\BranchContext::current()?->name }}</span>
                                @if ($subscription?->plan)
                                    <span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-medium">{{ $subscription->plan->name }} {{ __('plan') }}</span>
                                @endif
                            @endunless
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:grid lg:grid-cols-[220px_1fr] lg:gap-6 lg:items-start"
                x-data="{
                    active: 'profile-information',
                    isActive(id) { return this.active === id },
                    cardClass(id) {
                        return this.isActive(id) ? 'border-brand-200 ring-2 ring-brand-100' : 'border-gray-100';
                    },
                    navClass(id) {
                        return this.isActive(id) ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50';
                    },
                }"
                x-init="
                    const sections = Array.from($el.querySelectorAll('[data-section]'));
                    const refY = 140;
                    const updateActive = () => {
                        const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 4;
                        if (atBottom) { active = sections[sections.length - 1].id; return; }
                        let current = sections[0]?.id;
                        for (const el of sections) {
                            if (el.getBoundingClientRect().top - refY <= 0) current = el.id;
                        }
                        if (current) active = current;
                    };
                    window.addEventListener('scroll', updateActive, { passive: true });
                ">
                <!-- Desktop section nav (anchors into the cards on the right) -->
                <nav class="hidden lg:block bg-white rounded-xl border border-gray-100 shadow-sm p-2 sticky top-24 space-y-0.5">
                    <a href="#profile-information" @click="active = 'profile-information'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('profile-information')">{{ __('Profile information') }}</a>
                    <a href="#notifications" @click="active = 'notifications'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('notifications')">{{ __('Notifications') }}</a>
                    <a href="#password" @click="active = 'password'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('password')">{{ __('Password & security') }}</a>
                </nav>

                <div class="space-y-6 min-w-0">
                    <div id="profile-information" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('profile-information')">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div id="notifications" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('notifications')">
                        @include('profile.partials.notification-settings-form')
                    </div>

                    <div id="password" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('password')">
                        @include('profile.partials.update-password-form')
                    </div>

                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="flex items-center justify-center gap-2 bg-white rounded-2xl border border-gray-100 shadow-sm py-3.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                        {{ __('Log out') }}
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
