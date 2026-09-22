{{-- Expects the enclosing x-data to expose: confirmOpen, confirmForm, confirmName, confirmActive --}}
<div x-show="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="confirmOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-900/50" @click="confirmOpen = false"></div>

    <div x-show="confirmOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center">

        <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-5"
            :class="confirmActive ? 'bg-red-50' : 'bg-green-50'">
            <svg x-show="confirmActive" class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg x-show="!confirmActive" class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold" :class="confirmActive ? 'text-red-600' : 'text-green-600'"
            x-text="confirmActive ? '{{ __('Mark as Inactive?') }}' : '{{ __('Mark as Active?') }}'"></h3>

        <p class="text-sm text-gray-500 mt-2">
            <span x-show="confirmActive" x-text="confirmName + ' {{ __('will no longer be able to log in until you activate them again.') }}'"></span>
            <span x-show="!confirmActive" x-text="confirmName + ' {{ __('will be able to log in again.') }}'"></span>
        </p>

        <div class="mt-7 flex gap-3">
            <button type="button" @click="confirmOpen = false"
                class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                {{ __('Cancel') }}
            </button>
            <button type="button" @click="confirmForm.submit(); confirmOpen = false"
                class="flex-1 px-4 py-2.5 rounded-lg text-white text-sm font-medium"
                :class="confirmActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'">
                <span x-text="confirmActive ? '{{ __('Yes, Mark Inactive') }}' : '{{ __('Yes, Mark Active') }}'"></span>
            </button>
        </div>
    </div>
</div>
