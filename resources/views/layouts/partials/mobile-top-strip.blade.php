<div class="lg:hidden print:hidden border-b border-gray-100 px-4 py-2 flex items-center justify-between gap-3">
    <div class="min-w-0 flex-1">
        @if (Auth::user()->isAdmin())
            <form method="POST" action="{{ route('branches.switch') }}">
                @csrf
                <select name="branch_id" id="mobile-branch-switcher" onchange="window.showCubeLoader(); this.form.submit()"
                    class="max-w-[170px] text-sm font-medium text-gray-700 border-0 bg-gray-100 rounded-full pl-3 pr-7 py-1.5 focus:ring-2 focus:ring-indigo-500 truncate">
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
        @include('layouts.partials.notification-bell')

        <a href="{{ route('profile.edit') }}" aria-label="{{ __('Profile') }}" class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </a>
    </div>
</div>
