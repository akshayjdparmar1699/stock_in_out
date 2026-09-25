<div x-data="pwaInstallBanner()" x-show="show" style="display: none;"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed inset-x-0 bottom-0 z-50 p-4 flex justify-center pointer-events-none">
    <div class="pointer-events-auto max-w-sm w-full bg-white rounded-xl shadow-xl border border-gray-200 p-4 flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
            <x-application-logo class="w-6 h-6 text-white" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-gray-800">{{ __('Install') }} {{ config('app.name') }}</div>
            <p class="text-xs text-gray-500 mt-0.5" x-show="!isIos">
                {{ __('Add this app to your home screen for quick, full-screen access — no browser bar, works like a real app.') }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5" x-show="isIos" style="display: none;">
                {{ __('Tap the Share icon below, then "Add to Home Screen".') }}
            </p>
            <div class="mt-3 flex gap-2">
                <button type="button" x-show="!isIos" style="display: none;" @click="install()"
                    class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
                    {{ __('Install') }}
                </button>
                <button type="button" @click="dismiss()" class="px-3 py-1.5 rounded-md border border-gray-300 text-gray-600 text-xs font-medium hover:bg-gray-50">
                    {{ __('Not now') }}
                </button>
            </div>
        </div>
        <button type="button" @click="dismiss()" class="shrink-0 text-gray-400 hover:text-gray-600" aria-label="{{ __('Dismiss') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
