{{-- Expects the enclosing x-data to expose: deleteOpen, deleteForm, deleteLabel --}}
<div x-show="deleteOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div x-show="deleteOpen" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-900/50" @click="deleteOpen = false"></div>

    <div x-show="deleteOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center">

        <div class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-5 bg-red-50">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-red-600">{{ __('Delete this record?') }}</h3>

        <p class="text-sm text-gray-500 mt-2">
            <span x-text="deleteLabel"></span> {{ __('will be permanently deleted. This cannot be undone.') }}
        </p>

        <div class="mt-7 flex gap-3">
            <button type="button" @click="deleteOpen = false"
                class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                {{ __('Cancel') }}
            </button>
            <button type="button" @click="deleteForm.submit(); deleteOpen = false"
                class="flex-1 px-4 py-2.5 rounded-lg text-white text-sm font-medium bg-red-600 hover:bg-red-700">
                {{ __('Yes, Delete') }}
            </button>
        </div>
    </div>
</div>
