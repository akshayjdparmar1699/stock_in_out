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
        <div x-data="notificationBell()" class="relative">
            <button type="button" @click="toggle()" aria-label="{{ __('Notifications') }}" class="relative p-2 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span x-show="unreadCount > 0" style="display: none;" x-text="unreadCount > 99 ? '99+' : unreadCount"
                    class="absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] font-semibold leading-4 text-center"></span>
            </button>

            <div x-show="open" @click.outside="open = false" style="display: none;"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden z-50">
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100">
                    <span class="text-sm font-semibold text-gray-700">{{ __('Notifications') }}</span>
                    <button type="button" x-show="unreadCount > 0" style="display: none;" @click="markAllRead()" class="text-xs text-indigo-600 hover:text-indigo-800">
                        {{ __('Mark all read') }}
                    </button>
                </div>

                <button type="button" x-show="canUsePush && pushPermission === 'default'" style="display: none;" @click="enablePush()"
                    class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border-b border-gray-100">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    {{ __('Turn on alerts for this device') }}
                </button>

                <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                    <template x-if="!loading && notifications.length === 0">
                        <p class="px-4 py-6 text-sm text-gray-400 text-center">{{ __("You're all caught up") }}</p>
                    </template>

                    <template x-for="n in notifications" :key="n.id">
                        <form method="POST" :action="'/notifications/' + n.id + '/read'">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex gap-2 items-start" :class="!n.read ? 'bg-indigo-50/60' : ''">
                                <span class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" :class="n.read ? 'bg-transparent' : 'bg-indigo-500'"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-gray-800 font-medium truncate" x-text="n.title"></span>
                                    <span class="block text-xs text-gray-400 truncate" x-text="n.body"></span>
                                    <span class="block text-[11px] text-gray-300 mt-0.5" x-text="n.created_at"></span>
                                </span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        <a href="{{ route('profile.edit') }}" aria-label="{{ __('Profile') }}" class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </a>
    </div>
</div>
