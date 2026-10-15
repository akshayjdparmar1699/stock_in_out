<div class="flex items-start gap-3">
    <span class="shrink-0 w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
    </span>
    <div class="min-w-0">
        <h3 class="font-semibold text-gray-800">{{ __('Branch') }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="hidden lg:inline">{{ __('Switch which branch you are currently working in.') }}</span>
            <span class="lg:hidden">{{ __('Branch you are working in') }}</span>
        </p>
    </div>
</div>

<div class="mt-5">
    @if ($user->isAdmin())
        <form method="POST" action="{{ route('branches.switch') }}">
            @csrf
            <div class="hidden lg:flex items-center justify-between gap-4 bg-brand-50/60 border border-brand-100 rounded-xl px-4 py-3.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">{{ __('Working in') }}</p>
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ \App\Services\BranchContext::current()?->name }}</p>
                    </div>
                </div>
                <select name="branch_id" id="profile-branch-switcher" onchange="window.showCubeLoader(); this.form.submit()"
                    class="w-56 shrink-0 rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @foreach (\App\Models\Branch::where('company_id', Auth::user()->company_id)->orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}" @selected(\App\Services\BranchContext::id() === $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <select name="branch_id" onchange="window.showCubeLoader(); this.form.submit()"
                class="lg:hidden w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                @foreach (\App\Models\Branch::where('company_id', Auth::user()->company_id)->orderBy('name')->get() as $branch)
                    <option value="{{ $branch->id }}" @selected(\App\Services\BranchContext::id() === $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @else
        <p class="text-sm text-gray-500">
            {{ __('You are working in') }} <span class="font-medium text-gray-800">{{ \App\Services\BranchContext::current()?->name }}</span>.
        </p>
    @endif
</div>
