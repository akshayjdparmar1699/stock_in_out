<div x-data="{
        show: false,
        async init() {
            // Give app.js a tick to set these window flags before reading them.
            await new Promise((r) => setTimeout(r, 50));
            this.show = window.canUsePushNotifications && window.pushPermissionState === 'default';
        },
        async enable() {
            await window.subscribeToPushNotifications();
            this.show = false;
        },
    }"
    x-show="show" style="display: none;"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/50">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6">
        <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">{{ __('Turn on alerts?') }}</h3>
        <p class="text-sm text-gray-500 mt-1.5">
            {{ __('Get notified the instant an item runs low or a customer goes over their credit limit — even when the app is closed.') }}
        </p>
        <div class="mt-5 flex gap-3">
            <button type="button" @click="show = false" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                {{ __('Not now') }}
            </button>
            <button type="button" @click="enable()" class="flex-1 px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                {{ __('Enable') }}
            </button>
        </div>
    </div>
</div>
