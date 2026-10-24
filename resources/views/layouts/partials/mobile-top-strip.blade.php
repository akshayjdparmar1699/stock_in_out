<div class="lg:hidden print:hidden border-b border-gray-100 px-4 py-2 flex items-center justify-between gap-3">
    <div class="min-w-0 flex-1">
        @if (Auth::user()->isAdmin())
            <form method="POST" action="{{ route('branches.switch') }}">
                @csrf
                <select name="branch_id" id="mobile-branch-switcher" onchange="window.showCubeLoader(); this.form.submit()"
                    class="max-w-[170px] text-sm font-medium text-gray-700 border-0 bg-gray-100 rounded-full pl-3 pr-7 py-1.5 focus:ring-2 focus:ring-brand-500 truncate">
                    @foreach (\App\Models\Branch::where('company_id', Auth::user()->company_id)->orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}" @selected(\App\Services\BranchContext::id() === $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @else
            <span class="inline-block max-w-[170px] truncate text-sm font-medium text-gray-700 bg-gray-100 rounded-full px-3 py-1.5">
                {{ \App\Services\BranchContext::current()?->name }}
            </span>
        @endif
    </div>

    <div class="flex items-center gap-1 shrink-0">
        @if (Auth::user()->isAdmin())
            <a href="{{ route('settings.edit') }}" aria-label="{{ __('Settings') }}" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        @endif
        @include('layouts.partials.notification-bell')

        <a href="{{ route('profile.edit') }}" aria-label="{{ __('Profile') }}" class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold shrink-0 overflow-hidden">
            @if (Auth::user()->avatarUrl())
                <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            @endif
        </a>
    </div>
</div>
