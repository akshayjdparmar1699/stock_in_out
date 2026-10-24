{{-- Expects the enclosing x-data to expose: planLimitOpen, planLimitMessage.
     Triggered from an "+ Add ..." button's @click when the server-computed
     limit check says the company's plan has no room left, instead of
     letting them fill the whole form and only finding out on submit. --}}
<div x-show="planLimitOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="planLimitOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-900/50" @click="planLimitOpen = false"></div>

    <div x-show="planLimitOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center">

        <div class="mx-auto w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.502-3.032-1.502-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-gray-900">{{ __('Plan limit reached') }}</h3>

        <p class="text-sm text-gray-500 mt-2" x-text="planLimitMessage"></p>

        <div class="mt-7">
            <button type="button" @click="planLimitOpen = false"
                class="w-full px-4 py-2.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium">
                {{ __('OK') }}
            </button>
        </div>
    </div>
</div>
