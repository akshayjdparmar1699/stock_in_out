<div class="flex items-start gap-3">
    <span class="shrink-0 w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
    </span>
    <div class="min-w-0">
        <h3 class="font-semibold text-red-600">{{ __('Delete account') }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="hidden lg:inline">{{ __('Once deleted, all of your account data is permanently removed. Download anything you want to keep first.') }}</span>
            <span class="lg:hidden">{{ __('All your data is removed permanently') }}</span>
        </p>
    </div>
</div>

<div class="flex flex-col lg:flex-row lg:justify-end gap-3 mt-5">
    <a href="{{ route('profile.download') }}" class="order-2 lg:order-1">
        <x-secondary-button type="button" class="w-full justify-center normal-case tracking-normal text-sm font-semibold">
            <span class="lg:hidden">{{ __('Download my data first') }}</span>
            <span class="hidden lg:inline">{{ __('Download my data') }}</span>
        </x-secondary-button>
    </a>

    <x-danger-button
        class="order-1 lg:order-2 w-full lg:w-auto justify-center normal-case tracking-normal text-sm"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete account') }}</x-danger-button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Are you sure you want to delete your account?') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <div class="mt-6">
            <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-3/4"
                placeholder="{{ __('Password') }}"
            />

            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ms-3">
                {{ __('Delete Account') }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
