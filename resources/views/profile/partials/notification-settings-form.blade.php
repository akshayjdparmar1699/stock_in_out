<div x-data="{
        canUsePush: window.canUsePushNotifications,
        permission: window.pushPermissionState,
        enabling: false,
        async toggle() {
            if (this.permission === 'denied' || this.enabling) return;
            if (this.permission === 'granted') return;
            this.enabling = true;
            await window.subscribeToPushNotifications();
            this.permission = window.pushPermissionState;
            this.enabling = false;
        },
    }">
    <div class="flex items-start gap-3">
        <span class="shrink-0 w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </span>
        <div class="min-w-0">
            <h3 class="font-semibold text-gray-800">{{ __('Notifications') }}</h3>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="hidden lg:inline">{{ __('One switch for all alerts: low stock, credit limit and customer follow up.') }}</span>
                <span class="lg:hidden">{{ __('One switch for all alerts') }}</span>
            </p>
        </div>
    </div>

    <div class="mt-5">
        <template x-if="!canUsePush">
            <p class="text-sm text-gray-500">{{ __("This browser doesn't support notifications.") }}</p>
        </template>

        <template x-if="canUsePush && permission === 'denied'">
            <p class="text-sm text-red-600">
                {{ __('Notifications are blocked for this site in your browser. Enable them from your browser\'s site settings to turn this on.') }}
            </p>
        </template>

        <template x-if="canUsePush && permission !== 'denied'">
            <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 text-sm">
                            <span class="hidden lg:inline" x-text="permission === 'granted' ? '{{ __('All alerts on this device') }}' : '{{ __('Turn on alerts') }}'"></span>
                            <span class="lg:hidden" x-text="permission === 'granted' ? '{{ __('All alerts on this phone') }}' : '{{ __('Turn on alerts') }}'"></span>
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-show="permission === 'granted'">{{ __('On: you will get every alert below on this device') }}</span>
                            <span x-show="permission !== 'granted'" style="display: none;" x-text="enabling ? '{{ __('Enabling…') }}' : '{{ __('Off: tap to enable on this device') }}'"></span>
                        </p>
                    </div>
                    <button type="button" @click="toggle()" :disabled="enabling"
                        class="shrink-0 relative inline-flex h-7 w-12 items-center rounded-full transition-colors disabled:opacity-50"
                        :class="permission === 'granted' ? 'bg-brand-600' : 'bg-gray-300'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
                            :class="permission === 'granted' ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2.5 mt-4">
                    <div class="flex items-start gap-2.5 bg-white rounded-lg px-3 py-2.5">
                        <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5"
                            :class="permission === 'granted' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ __('Low stock') }}</p>
                            <p class="text-xs text-gray-500">
                                <span class="hidden lg:inline">{{ __('Item drops below its minimum level') }}</span>
                                <span class="lg:hidden">{{ __('Item drops below minimum') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 bg-white rounded-lg px-3 py-2.5">
                        <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5"
                            :class="permission === 'granted' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ __('Credit limit') }}</p>
                            <p class="text-xs text-gray-500">
                                <span class="hidden lg:inline">{{ __('Customer goes over their credit limit') }}</span>
                                <span class="lg:hidden">{{ __('Customer goes over limit') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 bg-white rounded-lg px-3 py-2.5">
                        <span class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5"
                            :class="permission === 'granted' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400'">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ __('Customer follow up') }}</p>
                            <p class="text-xs text-gray-500">{{ __('No purchase in the last 3 days') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <p class="text-xs text-gray-400 mt-4 hidden lg:block">
            {{ __('Alerts are per device — turning them on here only enables them on the phone or browser you are using now.') }}
        </p>
    </div>
</div>
