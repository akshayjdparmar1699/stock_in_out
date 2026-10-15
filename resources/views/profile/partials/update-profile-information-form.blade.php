<div class="flex items-start gap-3">
    <span class="shrink-0 w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
        </svg>
    </span>
    <div class="min-w-0">
        <h3 class="font-semibold text-gray-800">{{ __('Profile information') }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="hidden lg:inline">{{ __("Update your name and email address.") }}</span>
            <span class="lg:hidden">{{ __('Your name and email') }}</span>
        </p>
    </div>
</div>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div>
            <p class="text-sm text-gray-800">
                {{ __('Your email address is unverified.') }}

                <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                    {{ __('Click here to re-send the verification email.') }}
                </button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="mt-2 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
        <x-secondary-button type="button" onclick="history.back()" class="hidden lg:inline-flex normal-case tracking-normal text-sm font-semibold">{{ __('Cancel') }}</x-secondary-button>
        <x-primary-button class="w-full lg:w-auto justify-center">{{ __('Save changes') }}</x-primary-button>

        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
