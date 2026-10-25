<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settings') }}</h2>
                <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Set up how your bills, stock and team work') }}</p>
            </div>
        </div>
    </x-slot>

    <link rel="stylesheet" href="{{ asset('css/wnv-settings.css') }}">

    <div x-data="{ mobileScreen: 'home' }">

        {{-- ============================================================
             MOBILE — one screen at a time (no new routes: mobileScreen
             just toggles which block is visible), matching the app's own
             existing mobile-vs-desktop split (CSS classes, same page).
        ============================================================ --}}
        <div class="lg:hidden">

            {{-- Home: branch chip + grouped settings list --}}
            <div x-show="mobileScreen === 'home'" class="wnv-settings">
                <div class="wnv-hero">
                    <div class="wnv-hero__bar">
                        <a href="{{ route('dashboard') }}" class="wnv-icon-btn wnv-icon-btn--glass" aria-label="{{ __('Back') }}">
                            @include('settings.partials.icon', ['name' => 'back', 'size' => 20, 'stroke' => 2.4])
                        </a>
                        <h1 class="wnv-hero__title">{{ __('Settings') }}</h1>
                        <span class="wnv-icon-btn wnv-icon-btn--spacer" aria-hidden="true"></span>
                    </div>

                    <div class="wnv-branch-chip">
                        <span class="wnv-branch-chip__logo" aria-hidden="true">
                            <svg width="34" height="34" viewBox="0 0 120 120"><rect width="120" height="120" rx="30" fill="#FFFFFF"/><circle cx="30" cy="25" r="7" fill="#6A00D9"/><line x1="32" y1="42" x2="56" y2="90" stroke="#6A00D9" stroke-width="13" stroke-linecap="round"/><line x1="56" y1="90" x2="81" y2="45" stroke="#FFB020" stroke-width="13" stroke-linecap="round"/><polygon points="96,20 95,50 70,36" fill="#FFB020"/></svg>
                        </span>
                        <span class="wnv-branch-chip__text">
                            <span class="wnv-branch-chip__label">{{ __('Settings for branch') }}</span>
                            <span class="wnv-branch-chip__name">{{ $branch?->name }}</span>
                        </span>
                        @if ($subscription?->plan)
                            <span class="wnv-badge wnv-badge--gold">{{ $subscription->plan->name }}</span>
                        @endif
                    </div>

                    <label class="wnv-search">
                        <span class="wnv-search__icon">@include('settings.partials.icon', ['name' => 'search'])</span>
                        <input type="search" placeholder="{{ __('Search settings') }}" aria-label="{{ __('Search settings') }}" data-wnv-search>
                    </label>
                </div>

                <div class="wnv-body">
                    @if (session('status'))
                        <div class="wnv-toast" role="status">{{ session('status') }}</div>
                    @endif

                    <section class="wnv-group" data-wnv-group>
                        <h2 class="wnv-group__label">{{ __('Invoicing') }}</h2>
                        <div class="wnv-card">
                            <button type="button" @click="mobileScreen = 'invoice-format'" class="wnv-row" data-wnv-row="invoice format" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--purple">@include('settings.partials.icon', ['name' => 'format'])</span>
                                <span class="wnv-row__text">
                                    <span class="wnv-row__label">{{ __('Invoice format') }}</span>
                                    <span class="wnv-row__sub">{{ $formats[$selectedFormat]['name'] ?? '' }}</span>
                                </span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <button type="button" @click="mobileScreen = 'invoice-terms'" class="wnv-row" data-wnv-row="invoice terms" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--purple">@include('settings.partials.icon', ['name' => 'terms'])</span>
                                <span class="wnv-row__text"><span class="wnv-row__label">{{ __('Invoice terms') }}</span></span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <button type="button" @click="mobileScreen = 'default-bill-status'" class="wnv-row" data-wnv-row="default bill status" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--green">@include('settings.partials.icon', ['name' => 'check'])</span>
                                <span class="wnv-row__text">
                                    <span class="wnv-row__label">{{ __('Default bill status') }}</span>
                                    <span class="wnv-row__sub">{{ ucfirst($defaultPaymentStatus) }}</span>
                                </span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <button type="button" @click="mobileScreen = 'default-purchase-status'" class="wnv-row" data-wnv-row="default purchase status" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--amber">@include('settings.partials.icon', ['name' => 'truck'])</span>
                                <span class="wnv-row__text">
                                    <span class="wnv-row__label">{{ __('Default purchase status') }}</span>
                                    <span class="wnv-row__sub">{{ ucfirst($defaultPurchasePaymentStatus) }}</span>
                                </span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                        </div>
                    </section>

                    <section class="wnv-group" data-wnv-group>
                        <h2 class="wnv-group__label">{{ __('Business') }}</h2>
                        <div class="wnv-card">
                            <button type="button" @click="mobileScreen = 'gst'" class="wnv-row" data-wnv-row="gst & tax" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--amber">@include('settings.partials.icon', ['name' => 'percent'])</span>
                                <span class="wnv-row__text"><span class="wnv-row__label">{{ __('GST & tax') }}</span></span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <button type="button" @click="mobileScreen = 'subscription'" class="wnv-row" data-wnv-row="subscription" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--amber">@include('settings.partials.icon', ['name' => 'star'])</span>
                                <span class="wnv-row__text">
                                    <span class="wnv-row__label">{{ __('Subscription') }}</span>
                                    @if ($subscription?->plan)<span class="wnv-row__sub">{{ $subscription->plan->name }} {{ __('plan') }}</span>@endif
                                </span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <button type="button" @click="mobileScreen = 'branches'" class="wnv-row" data-wnv-row="branches" style="width: 100%; text-align: left; border: 0; background: none;">
                                <span class="wnv-row__icon wnv-tone--blue">@include('settings.partials.icon', ['name' => 'branch'])</span>
                                <span class="wnv-row__text"><span class="wnv-row__label">{{ __('Branches') }}</span></span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </button>
                            <a href="{{ route('users.index') }}" class="wnv-row" data-wnv-row="users roles permission">
                                <span class="wnv-row__icon wnv-tone--blue">@include('settings.partials.icon', ['name' => 'users'])</span>
                                <span class="wnv-row__text">
                                    <span class="wnv-row__label">{{ __('Users & roles') }}</span>
                                    <span class="wnv-row__sub">{{ __('Add people and set what each can do') }}</span>
                                </span>
                                <span class="wnv-row__chev">@include('settings.partials.icon', ['name' => 'chevron', 'stroke' => 2.2])</span>
                            </a>
                        </div>
                    </section>

                    <p class="wnv-empty" data-wnv-empty hidden>{{ __('No settings match your search.') }}</p>
                </div>
            </div>

            {{-- Invoice format --}}
            <div x-show="mobileScreen === 'invoice-format'" style="display: none;" class="wnv-settings wnv-settings--detail"
                x-data="{
                    selected: {{ \Illuminate\Support\Js::from($selectedFormat) }},
                    get previewUrl() {
                        return {{ \Illuminate\Support\Js::from(route('settings.invoice-format.preview')) }} + '?format=' + encodeURIComponent(this.selected);
                    }
                }">
                <form method="POST" action="{{ route('settings.invoice-format') }}">
                    @csrf
                    @method('PUT')

                    <header class="wnv-topbar">
                        <button type="button" @click="mobileScreen = 'home'" class="wnv-icon-btn wnv-icon-btn--soft" aria-label="{{ __('Back to settings') }}">
                            @include('settings.partials.icon', ['name' => 'back', 'size' => 20, 'stroke' => 2.4])
                        </button>
                        <div class="wnv-topbar__text">
                            <h1 class="wnv-topbar__title">{{ __('Invoice format') }}</h1>
                            <div class="wnv-topbar__sub">{{ $branch?->name }}</div>
                        </div>
                        <a :href="previewUrl" target="_blank" class="wnv-link">{{ __('Preview') }}</a>
                    </header>

                    <div class="wnv-body wnv-body--with-bar">
                        <p class="wnv-lead">{{ __('Pick how your bill looks when you print it or share it on WhatsApp.') }}</p>

                        <div class="wnv-format-grid">
                            @foreach ($formats as $key => $format)
                                <label class="wnv-format">
                                    <input type="radio" name="invoice_format" value="{{ $key }}" class="wnv-sr-only" data-name="{{ $format['name'] }}" @checked($selectedFormat === $key) @change="selected = '{{ $key }}'">
                                    <span class="wnv-format__card">
                                        <span class="wnv-format__preview">
                                            <img src="{{ asset($format['thumbnail']) }}" alt="{{ $format['name'] }}">
                                            @if ($key === config('invoice_formats.default'))
                                                <span class="wnv-badge wnv-badge--default">{{ __('Default') }}</span>
                                            @endif
                                            <span class="wnv-format__check" x-show="selected === '{{ $key }}'" style="display: none;">@include('settings.partials.icon', ['name' => 'tick', 'size' => 14, 'stroke' => 3])</span>
                                        </span>
                                        <span class="wnv-format__name">{{ $format['name'] }}</span>
                                        <span class="wnv-format__desc">{{ $format['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="wnv-note">
                            @include('settings.partials.icon', ['name' => 'info'])
                            <span>{{ __('Only new bills use the new format. Bills you already shared keep their old look.') }}</span>
                        </div>
                    </div>

                    <div class="wnv-bottom-bar" x-data="{ formats: {{ \Illuminate\Support\Js::from(collect($formats)->map(fn ($f) => $f['name'])) }} }">
                        <div class="wnv-bottom-bar__row">
                            <span class="wnv-muted">{{ __('Selected') }}</span>
                            <strong x-text="formats[selected]"></strong>
                        </div>
                        <div class="wnv-bottom-bar__actions">
                            <a :href="previewUrl" target="_blank" class="wnv-btn wnv-btn--outline">{{ __('Preview bill') }}</a>
                            <button type="submit" class="wnv-btn wnv-btn--primary">{{ __('Save format') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Invoice terms / Default bill status / Default purchase status / GST / Subscription / Branches —
                 each reuses the exact same partial (and so the exact same routes/controllers) the desktop
                 cards below use; only the surrounding chrome differs. --}}
            @foreach ([
                'invoice-terms' => ['title' => __('Invoice terms'), 'view' => 'profile.partials.invoice-terms-form'],
                'default-bill-status' => ['title' => __('Default bill status'), 'view' => 'profile.partials.default-payment-status-form'],
                'default-purchase-status' => ['title' => __('Default purchase status'), 'view' => 'profile.partials.default-purchase-payment-status-form'],
                'gst' => ['title' => __('GST & tax'), 'view' => 'profile.partials.gst-settings-form'],
                'subscription' => ['title' => __('Subscription'), 'view' => 'profile.partials.plan-info'],
                'branches' => ['title' => __('Branches'), 'view' => 'profile.partials.branch-settings-form'],
            ] as $screen => $section)
                <div x-show="mobileScreen === '{{ $screen }}'" style="display: none;" class="wnv-settings wnv-settings--detail">
                    <header class="wnv-topbar">
                        <button type="button" @click="mobileScreen = 'home'" class="wnv-icon-btn wnv-icon-btn--soft" aria-label="{{ __('Back to settings') }}">
                            @include('settings.partials.icon', ['name' => 'back', 'size' => 20, 'stroke' => 2.4])
                        </button>
                        <div class="wnv-topbar__text">
                            <h1 class="wnv-topbar__title">{{ $section['title'] }}</h1>
                        </div>
                    </header>
                    <div class="wnv-body">
                        @if (session('status'))
                            <div class="wnv-toast" role="status">{{ session('status') }}</div>
                        @endif
                        @include($section['view'])
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============================================================
             DESKTOP — unchanged long-scroll page with an anchor nav.
        ============================================================ --}}
        <div class="hidden lg:block py-6 sm:py-12 pb-28 lg:pb-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-[220px_1fr] lg:gap-6 lg:items-start"
                    x-data="{
                        active: 'invoice-format',
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

                    <nav class="hidden lg:block bg-white rounded-xl border border-gray-100 shadow-sm p-2 sticky top-24 space-y-0.5">
                        <p class="px-3 pt-1 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ __('Invoicing') }}</p>
                        <a href="#invoice-format" @click="active = 'invoice-format'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('invoice-format')">{{ __('Invoice format') }}</a>
                        <a href="#invoice-terms" @click="active = 'invoice-terms'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('invoice-terms')">{{ __('Invoice terms') }}</a>
                        <a href="#default-payment-status" @click="active = 'default-payment-status'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('default-payment-status')">{{ __('Default bill status') }}</a>
                        <a href="#default-purchase-payment-status" @click="active = 'default-purchase-payment-status'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('default-purchase-payment-status')">{{ __('Default purchase status') }}</a>

                        <p class="px-3 pt-3 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ __('Business') }}</p>
                        <a href="#gst" @click="active = 'gst'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('gst')">{{ __('GST & tax') }}</a>
                        <a href="#subscription" @click="active = 'subscription'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('subscription')">{{ __('Subscription') }}</a>
                        <a href="#branches" @click="active = 'branches'" class="block px-3 py-2 rounded-lg text-sm font-medium" :class="navClass('branches')">{{ __('Branches') }}</a>
                        <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">{{ __('Users & roles') }}</a>
                    </nav>

                    <div class="space-y-6 min-w-0">

                        {{-- Invoice format --}}
                        <div id="invoice-format" data-section class="bg-white rounded-2xl shadow-sm border p-5 lg:p-6 scroll-mt-24 transition-colors" :class="cardClass('invoice-format')"
                            x-data="{
                                selected: {{ \Illuminate\Support\Js::from($selectedFormat) }},
                                get previewUrl() {
                                    return {{ \Illuminate\Support\Js::from(route('settings.invoice-format.preview')) }} + '?format=' + encodeURIComponent(this.selected);
                                }
                            }">
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

                        <div id="invoice-terms" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('invoice-terms')">
                            @include('profile.partials.invoice-terms-form')
                        </div>

                        <div id="default-payment-status" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('default-payment-status')">
                            @include('profile.partials.default-payment-status-form')
                        </div>

                        <div id="default-purchase-payment-status" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('default-purchase-payment-status')">
                            @include('profile.partials.default-purchase-payment-status-form')
                        </div>

                        <div id="gst" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('gst')">
                            @include('profile.partials.gst-settings-form')
                        </div>

                        <div id="subscription" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('subscription')">
                            @include('profile.partials.plan-info')
                        </div>

                        <div id="branches" data-section class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6 scroll-mt-24 transition-colors" :class="cardClass('branches')">
                            @include('profile.partials.branch-settings-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/wnv-settings-search.js') }}" defer></script>
</x-app-layout>
