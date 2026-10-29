<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Pricing') }} — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $trialPlan = $plans->firstWhere('key', \App\Models\Plan::KEY_FREE_TRIAL);
    @endphp
    <body class="font-sans text-gray-900 antialiased bg-gray-50"
        x-data="{
            selectedPlan: {{ $selectedPlanId }},
            term: 1,
            plans: {{ \Illuminate\Support\Js::from($plans->map(fn ($p) => [
                'id' => $p->id,
                'key' => $p->key,
                'name' => $p->name,
                // The trial is a flat one-time charge with no multi-year
                // concept at all, so its 1/3 figures are identical on
                // purpose — the toggle simply has nothing to change there.
                'price1' => $p->isFreeTrial() ? $p->price : $p->priceForYears(1),
                'price3' => $p->isFreeTrial() ? $p->price : $p->priceForYears(3),
                'period1' => $p->billing_period === 'trial' ? ('for ' . $p->trial_days . ' days') : ($p->billing_period === 'yearly' ? '/ year' : '/ month'),
                'period3' => $p->billing_period === 'trial' ? ('for ' . $p->trial_days . ' days') : ($p->billing_period === 'yearly' ? '/ 3 years' : '/ month'),
                'branchText' => $p->hasUnlimitedBranches() ? 'unlimited branches' : ($p->branch_limit == 1 ? '1 branch' : $p->branch_limit . ' branches'),
                'cta' => $p->isFreeTrial() ? 'Start trial' : ('Continue with ' . $p->name),
            ])) }},
            get plan() { return this.plans.find((p) => p.id === this.selectedPlan); },
            get price() { return this.term === 3 ? this.plan.price3 : this.plan.price1; },
            get period() { return this.term === 3 ? this.plan.period3 : this.plan.period1; },
        }">

        <!-- Top bar -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('get-started') }}" class="hidden lg:flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="w-8 h-8" />
                    <span class="font-semibold text-gray-800">{{ config('app.name') }}</span>
                </a>

                <a href="{{ url()->previous() === url()->current() ? url('/') : url()->previous() }}" aria-label="{{ __('Back') }}" class="lg:hidden w-9 h-9 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </a>

                <!-- Desktop step indicator -->
                <div class="hidden lg:flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-2 font-semibold text-brand-700">
                        <span class="w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs">1</span>
                        {{ __('Choose plan') }}
                    </span>
                    <span class="w-10 h-px bg-gray-200"></span>
                    <span class="flex items-center gap-2 text-gray-400">
                        <span class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-xs">2</span>
                        {{ __('Business details') }}
                    </span>
                    <span class="w-10 h-px bg-gray-200"></span>
                    <span class="flex items-center gap-2 text-gray-400">
                        <span class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-xs">3</span>
                        {{ __('Start billing') }}
                    </span>
                </div>

                <!-- Mobile step dots -->
                <div class="lg:hidden flex items-center gap-1.5">
                    <span class="w-5 h-1.5 rounded-full bg-[#FFB020]"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                </div>

                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600">
                    {{ __('Already have an account?') }} <span class="text-brand-600 hover:text-brand-700 underline">{{ __('Log in') }}</span>
                </a>
            </div>
        </div>

        <!-- Hero -->
        <div class="relative bg-gradient-to-br from-brand-600 to-brand-800 text-white px-4 sm:px-6 lg:px-8 pt-10 pb-28 lg:pb-32 overflow-hidden">
            <div class="absolute -left-20 bottom-0 w-64 h-64 rounded-full bg-white/5"></div>
            <div class="absolute -right-16 top-10 w-56 h-56 rounded-full bg-white/5 hidden lg:block"></div>

            <div class="relative max-w-3xl mx-auto text-center">
                <span class="hidden lg:inline-flex items-center gap-2 text-xs font-medium bg-white/10 rounded-full px-3 py-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FFB020]"></span>
                    {{ $trialPlan->price > 0 ? __('Start for :price, upgrade when you are ready', ['price' => '₹'.$trialPlan->price]) : __('Start free, upgrade when you are ready') }}
                </span>

                <p class="lg:hidden text-xs font-semibold tracking-wide text-[#FFB020]">{{ __('STEP 1 OF 3') }}</p>

                <h1 class="mt-4 lg:mt-5 text-3xl lg:text-5xl font-extrabold leading-tight">
                    <span class="lg:hidden">{{ __('Pick your') }} <span class="text-[#FFB020]">{{ __('plan') }}</span></span>
                    <span class="hidden lg:inline">{{ __('Pick the plan that fits') }} <span class="text-[#FFB020]">{{ __('your business') }}</span></span>
                </h1>

                <p class="mt-4 text-white/80 text-sm lg:text-base max-w-xl mx-auto">
                    @if ($trialPlan->price > 0)
                        <span class="lg:hidden">{{ __('Try everything for :days days for just :price.', ['days' => $trialPlan->trial_days, 'price' => '₹'.$trialPlan->price]) }}</span>
                        <span class="hidden lg:inline">{{ __('Try everything for :days days for just :price. Upgrade as your branches and team grow.', ['days' => $trialPlan->trial_days, 'price' => '₹'.$trialPlan->price]) }}</span>
                    @else
                        <span class="lg:hidden">{{ __('Try everything free for :days days.', ['days' => $trialPlan->trial_days]) }}</span>
                        <span class="hidden lg:inline">{{ __('Try everything free for :days days. Upgrade as your branches and team grow.', ['days' => $trialPlan->trial_days]) }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Plan cards -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 lg:-mt-24 relative">
            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center bg-white rounded-full p-1 shadow-sm">
                    <button type="button" @click="term = 1" class="px-4 py-1.5 rounded-full text-sm font-semibold transition-colors" :class="term === 1 ? 'bg-brand-600 text-white' : 'text-gray-500'">
                        {{ __('1 year') }}
                    </button>
                    <button type="button" @click="term = 3" class="px-4 py-1.5 rounded-full text-sm font-semibold transition-colors flex items-center gap-1.5" :class="term === 3 ? 'bg-brand-600 text-white' : 'text-gray-500'">
                        {{ __('3 years') }}
                        <span class="px-1.5 py-0.5 rounded-full bg-[#FFB020] text-brand-900 text-[10px] font-bold">{{ __('Save 30%') }}</span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-5 lg:grid lg:grid-cols-4 lg:gap-5 lg:items-stretch">
                @foreach ($plans as $plan)
                    @php
                        $isGold = (bool) $plan->is_popular;
                        $selectedCond = "selectedPlan === {$plan->id}";
                        $fillClasses = $isGold
                            ? 'bg-gradient-to-br from-brand-600 to-brand-800 border-[#FFB020] shadow-xl'
                            : 'bg-gradient-to-br from-brand-600 to-brand-800 border-brand-800 shadow-xl';
                        $accent = $isGold ? '#FFB020' : null;
                    @endphp
                    <div class="relative rounded-2xl p-6 flex flex-col border-2 transition-colors {{ $isGold ? 'lg:-mt-3' : '' }}"
                        :class="{{ $selectedCond }} ? '{{ $fillClasses }}' : 'bg-white border-gray-200 shadow-sm'">
                        @if ($isGold)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#FFB020] text-brand-900 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide whitespace-nowrap">{{ __('Most popular') }}</span>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                                    :class="{{ $selectedCond }} ? '{{ $isGold ? 'bg-[#FFB020] text-white' : 'bg-white/15 text-white' }}' : '{{ $plan->isFreeTrial() ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}'">
                                    {{ $plan->isFreeTrial() ? $plan->trial_days : strtoupper(substr($plan->name, 0, 1)) }}
                                </span>
                                <h3 class="font-bold text-lg truncate" :class="{{ $selectedCond }} ? 'text-white' : 'text-gray-900'">{{ $plan->name }}</h3>
                            </div>
                            <button type="button" @click="selectedPlan = {{ $plan->id }}" aria-label="{{ __('Select') }} {{ $plan->name }}"
                                class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="{{ $selectedCond }} ? '{{ $isGold ? 'border-[#FFB020]' : 'border-white' }}' : 'border-gray-300'">
                                <span x-show="{{ $selectedCond }}" style="display:none" class="w-2.5 h-2.5 rounded-full {{ $isGold ? "bg-[#FFB020]" : 'bg-white' }}"></span>
                            </button>
                        </div>

                        <p class="text-sm mt-3" :class="{{ $selectedCond }} ? 'text-white/80' : 'text-gray-500'">{{ $plan->tagline }}</p>

                        <div class="mt-5 flex items-end gap-1.5 flex-wrap">
                            <span class="text-3xl font-extrabold" :class="{{ $selectedCond }} ? 'text-white' : 'text-gray-900'">
                                @if ($plan->isFreeTrial())
                                    {{ $plan->price > 0 ? '₹'.number_format($plan->price) : __('Free') }}
                                @else
                                    <span x-show="term === 1">&#8377;{{ number_format($plan->priceForYears(1)) }}</span>
                                    <span x-show="term === 3" style="display: none;">&#8377;{{ number_format($plan->priceForYears(3)) }}</span>
                                @endif
                            </span>
                            <span class="text-sm mb-1" :class="{{ $selectedCond }} ? 'text-white/70' : 'text-gray-400'">
                                @if ($plan->isFreeTrial())
                                    {{ __('for :days days', ['days' => $plan->trial_days]) }}
                                @else
                                    <span x-show="term === 1">{{ __('/ year') }}</span>
                                    <span x-show="term === 3" style="display: none;">{{ __('/ 3 years') }}</span>
                                @endif
                            </span>
                        </div>
                        @unless ($plan->isFreeTrial())
                            <p class="text-xs" :class="{{ $selectedCond }} ? 'text-white/60' : 'text-gray-400'">
                                <span x-show="term === 1">{{ __('≈ :amount / month', ['amount' => '₹'.number_format($plan->monthlyEquivalent(1))]) }}</span>
                                <span x-show="term === 3" style="display: none;">{{ __('≈ :amount / month', ['amount' => '₹'.number_format($plan->monthlyEquivalent(3))]) }}</span>
                            </p>
                        @endunless

                        <span class="inline-block mt-3 self-start px-3 py-1 rounded-full text-xs font-medium"
                            :class="{{ $selectedCond }} ? 'bg-white/15 text-white' : '{{ $plan->isFreeTrial() ? 'bg-green-50 text-green-700' : 'bg-brand-50 text-brand-700' }}'">
                            @if ($plan->hasUnlimitedBranches())
                                {{ __('Unlimited branches') }}
                            @else
                                {{ trans_choice('{1} 1 branch|[2,*] :count branches', $plan->branch_limit, ['count' => $plan->branch_limit]) }}
                            @endif
                            {{ $plan->isFreeTrial() ? __(' · :days days only', ['days' => $plan->trial_days]) : '' }}
                        </span>

                        <div class="border-t mt-5 pt-4 space-y-2.5 flex-1" :class="{{ $selectedCond }} ? 'border-white/15' : 'border-gray-100'">
                            @foreach ($plan->features as $feature)
                                <div class="flex items-start gap-2 text-sm" :class="{{ $selectedCond }} ? 'text-white/90' : 'text-gray-700'">
                                    <svg class="w-4 h-4 mt-0.5 shrink-0" :class="{{ $selectedCond }} ? '{{ $isGold ? 'text-[#FFB020]' : 'text-white' }}' : 'text-green-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span>{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" @click="selectedPlan = {{ $plan->id }}"
                            class="mt-6 w-full py-2.5 rounded-lg font-semibold text-sm transition-colors"
                            :class="{{ $selectedCond }} ? '{{ $isGold ? 'bg-[#FFB020] text-brand-900 hover:brightness-95' : 'bg-white text-brand-700 hover:bg-brand-50' }}' : 'bg-white border-2 border-brand-600 text-brand-700 hover:bg-brand-50'">
                            {{ $plan->isFreeTrial() ? ($plan->price > 0 ? __('Start trial for :price', ['price' => '₹'.$plan->price]) : __('Start free trial')) : __('Choose :name', ['name' => $plan->name]) }}
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Trust row -->
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 mt-8 text-sm text-gray-500">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ $trialPlan->price > 0 ? __('Trial is just :price', ['price' => '₹'.$trialPlan->price]) : __('No card needed for the trial') }}</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Cancel anytime') }}</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>{{ __('Your data stays safe when you upgrade') }}</span>
            </div>
        </div>

        <!-- Compare plans -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 lg:mt-20 pb-6">
            <h2 class="text-2xl lg:text-3xl font-bold text-center text-gray-900">{{ __('Compare plans') }}</h2>
            <p class="text-center text-gray-500 text-sm mt-2">{{ __('All plans include the full billing and stock system.') }}</p>

            @php
                $trial = $plans->firstWhere('key', \App\Models\Plan::KEY_FREE_TRIAL);
                $silver = $plans->firstWhere('key', \App\Models\Plan::KEY_SILVER);
                $gold = $plans->firstWhere('key', \App\Models\Plan::KEY_GOLD);
                $platinum = $plans->firstWhere('key', \App\Models\Plan::KEY_PLATINUM);
                $compareCols = [$trial, $silver, $gold, $platinum];
                $check = '<svg class="w-4 h-4 text-green-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
                $dash = '<span class="text-gray-300">&middot;</span>';
            @endphp

            <!-- Desktop table -->
            <div class="hidden lg:block mt-8 bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs text-gray-400 uppercase tracking-wide">
                            <th class="px-6 py-4 font-medium">{{ __('Feature') }}</th>
                            @foreach ($compareCols as $col)
                                <th class="px-6 py-4 font-medium {{ $col->is_popular ? 'bg-brand-50/60 text-brand-700' : '' }}">{{ $col->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('Price') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60 font-semibold text-brand-700' : '' }}">
                                    {{ $col->isFreeTrial() && $col->price == 0 ? __('Free') : '₹'.number_format($col->price).($col->isFreeTrial() ? '' : ' / '.__('year')) }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('How long') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60 font-semibold text-brand-700' : '' }}">
                                    {{ $col->isFreeTrial() ? __(':days days', ['days' => $col->trial_days]) : __('Yearly') }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('Branches') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60 font-semibold text-brand-700' : '' }}">
                                    {{ $col->hasUnlimitedBranches() ? __('Unlimited') : $col->branch_limit }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('Staff and partners') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60 font-semibold text-brand-700' : '' }}">
                                    {{ $col->hasUnlimitedStaff() ? __('Unlimited') : __('Up to :n', ['n' => $col->staff_limit]) }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('Logins (users)') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60 font-semibold text-brand-700' : '' }}">
                                    {{ $col->hasUnlimitedUsers() ? __('Unlimited') : __('Up to :n', ['n' => $col->user_limit]) }}
                                </td>
                            @endforeach
                        </tr>
                        @foreach ([
                            __('Billing and invoices'),
                            __('Stock, items and batches'),
                            __('Customers, suppliers, credit limits'),
                            __('Purchases and expenses'),
                            __('Alerts and WhatsApp reminders'),
                        ] as $row)
                            <tr>
                                <td class="px-6 py-3.5 text-gray-700">{{ $row }}</td>
                                @foreach ($compareCols as $col)
                                    <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60' : '' }}">{!! $check !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <td class="px-6 py-3.5 text-gray-700">{{ __('Separate stock for each branch') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-6 py-3.5 {{ $col->is_popular ? 'bg-brand-50/60' : '' }}">{!! $col->hasUnlimitedBranches() || $col->branch_limit > 1 ? $check : $dash !!}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile table -->
            <div class="lg:hidden mt-6 bg-white rounded-2xl border border-gray-100 overflow-hidden overflow-x-auto scrollbar-hide">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-400">
                            <th class="px-3 py-3 font-medium">{{ __('Feature') }}</th>
                            @foreach ($compareCols as $col)
                                <th class="px-3 py-3 font-medium whitespace-nowrap {{ $col->is_popular ? 'text-brand-700' : '' }}">{{ $col->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('Branches') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3 {{ $col->is_popular ? 'font-semibold text-brand-700' : '' }}">{{ $col->hasUnlimitedBranches() ? __('Unlimited') : $col->branch_limit }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('Staff') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3 {{ $col->is_popular ? 'font-semibold text-brand-700' : '' }}">{{ $col->hasUnlimitedStaff() ? __('Unlimited') : $col->staff_limit }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('Logins') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3 {{ $col->is_popular ? 'font-semibold text-brand-700' : '' }}">{{ $col->hasUnlimitedUsers() ? __('Unlimited') : $col->user_limit }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('How long') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3 {{ $col->is_popular ? 'font-semibold text-brand-700' : '' }}">{{ $col->isFreeTrial() ? __(':days days', ['days' => $col->trial_days]) : __('Yearly') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('Billing and stock') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3">{!! $check !!}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="px-3 py-3 text-gray-700">{{ __('Stock per branch') }}</td>
                            @foreach ($compareCols as $col)
                                <td class="px-3 py-3">{!! $col->hasUnlimitedBranches() || $col->branch_limit > 1 ? $check : $dash !!}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 lg:mt-16 pb-28 lg:pb-16">
            <h2 class="text-2xl lg:text-3xl font-bold text-center text-gray-900">{{ __('Common questions') }}</h2>

            @php
                $faqs = [
                    ['q' => __('What happens after :days days?', ['days' => $trialPlan->trial_days]), 'a' => __('Your free trial ends and you choose a paid plan to keep working. All your bills, stock and customers stay exactly as they are.')],
                    ['q' => __('Can I upgrade my plan later?'), 'a' => __('Yes. Upgrade any time you need more branches, staff or logins. Your existing data moves over automatically.')],
                    ['q' => __('What does "1 branch" mean in Silver?'), 'a' => __('You can run one shop or godown with full billing and stock. Gold and Platinum let you add more branches as you grow.')],
                ];

                if ($trialPlan->price > 0) {
                    $faqs[] = ['q' => __('Why does the free trial cost :price?', ['price' => '₹'.$trialPlan->price]), 'a' => __('It is a small charge just to verify your payment method and keep the trial free of spam signups — not a subscription charge.')];
                }
            @endphp

            <!-- Desktop grid -->
            <div class="hidden lg:grid lg:grid-cols-3 gap-4 mt-8">
                @foreach ($faqs as $faq)
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <p class="font-semibold text-gray-900 text-sm">{{ $faq['q'] }}</p>
                        <p class="text-sm text-gray-500 mt-1.5">{{ $faq['a'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Mobile accordion -->
            <div class="lg:hidden mt-6 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div>
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left">
                            <span class="font-medium text-gray-900 text-sm">{{ $faq['q'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open === {{ $i }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open === {{ $i }}" style="display: none;" class="px-4 pb-3.5 text-sm text-gray-500">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky bottom bar -->
        <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-100 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] z-40">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3 lg:py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs text-gray-400">{{ __('Selected plan') }}</p>
                    <p class="font-semibold text-gray-900 text-sm lg:text-base truncate">
                        <span x-text="plan.name"></span> &middot; <span x-text="price > 0 ? ('₹' + price.toLocaleString('en-IN') + ' ' + period) : ('{{ __('Free') }} ' + period)"></span>
                        <span class="hidden lg:inline"> &middot; <span x-text="plan.branchText"></span></span>
                    </p>
                </div>
                <a :href="'{{ route('get-started.details') }}?plan=' + selectedPlan + '&years=' + term"
                    class="shrink-0 inline-flex items-center justify-center px-5 lg:px-6 py-2.5 lg:py-3 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold rounded-lg">
                    <span x-text="plan.cta"></span>
                </a>
            </div>
        </div>
    </body>
</html>
