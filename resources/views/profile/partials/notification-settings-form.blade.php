<section x-data="{
        canUsePush: window.canUsePushNotifications,
        permission: window.pushPermissionState,
        enabling: false,
        async enable() {
            this.enabling = true;
            await window.subscribeToPushNotifications();
            this.permission = window.pushPermissionState;
            this.enabling = false;
        },
    }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Notifications') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Get alerted the instant stock runs low or a customer goes over their credit limit.') }}
        </p>
    </header>

    <div class="mt-5">
        <template x-if="!canUsePush">
            <p class="text-sm text-gray-500">
                {{ __("This browser doesn't support notifications.") }}
            </p>
        </template>

        <template x-if="canUsePush && permission === 'granted'">
            <div class="flex items-center gap-2 text-sm text-green-700">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                {{ __('Notifications are on for this device.') }}
            </div>
        </template>

        <template x-if="canUsePush && permission === 'denied'">
            <p class="text-sm text-red-600">
                {{ __('Notifications are blocked for this site in your browser. Enable them from your browser\'s site settings to turn this on.') }}
            </p>
        </template>

        <template x-if="canUsePush && permission === 'default'">
            <div>
                <p class="text-sm text-gray-500 mb-3">{{ __('Notifications are currently off on this device.') }}</p>
                <button type="button" @click="enable()" :disabled="enabling"
                    class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg">
                    <span x-show="!enabling">{{ __('Enable Notifications') }}</span>
                    <span x-show="enabling" style="display: none;">{{ __('Enabling…') }}</span>
                </button>
            </div>
        </template>

        <p class="text-xs text-gray-400 mt-4">
            {{ __('This is per device — turning it on here only enables alerts on the device/browser you\'re using right now.') }}
        </p>
    </div>
</section>
