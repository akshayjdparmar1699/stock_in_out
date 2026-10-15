<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Branch') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Switch which branch you are currently working in.') }}
        </p>
    </header>

    <div class="mt-5">
        @if (Auth::user()->isAdmin())
            <form method="POST" action="{{ route('branches.switch') }}" class="max-w-xs">
                @csrf
                <label for="profile-branch-switcher" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current branch') }}</label>
                <select name="branch_id" id="profile-branch-switcher" onchange="window.showCubeLoader(); this.form.submit()"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
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
</section>
