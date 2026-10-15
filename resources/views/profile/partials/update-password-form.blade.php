<div class="flex items-start gap-3">
    <span class="shrink-0 w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
        </svg>
    </span>
    <div class="min-w-0">
        <h3 class="font-semibold text-gray-800">{{ __('Password & security') }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="hidden lg:inline">{{ __('Use a long, random password to keep your account safe.') }}</span>
            <span class="lg:hidden">{{ __('Use a long, random password') }}</span>
        </p>
    </div>
</div>

<form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5"
    x-data="{
        showCurrent: false, showNew: false, showConfirm: false, pw: '',
        get hasLength() { return this.pw.length >= 8 },
        get hasNumber() { return /\d/.test(this.pw) },
        get hasCase() { return /[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw) },
        get hasSymbol() { return /[^A-Za-z0-9]/.test(this.pw) },
        get score() { return [this.hasLength, this.hasNumber, this.hasCase, this.hasSymbol].filter(Boolean).length },
    }">
    @csrf
    @method('put')

    <div>
        <x-input-label for="update_password_current_password" :value="__('Current password')" />
        <div class="relative mt-1">
            <input id="update_password_current_password" name="current_password" :type="showCurrent ? 'text' : 'password'"
                class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm pr-10"
                autocomplete="current-password" placeholder="{{ __('Enter current password') }}">
            <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                <svg x-show="!showCurrent" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <svg x-show="showCurrent" style="display: none;" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
            </button>
        </div>
        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div>
            <x-input-label for="update_password_password" :value="__('New password')" />
            <div class="relative mt-1">
                <input id="update_password_password" name="password" :type="showNew ? 'text' : 'password'" x-model="pw"
                    class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm pr-10"
                    autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}">
                <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                    <svg x-show="!showNew" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="showNew" style="display: none;" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm new password')" />
            <div class="relative mt-1">
                <input id="update_password_password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'"
                    class="block w-full border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm pr-10"
                    autocomplete="new-password" placeholder="{{ __('Type it again') }}">
                <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <svg x-show="showConfirm" style="display: none;" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>
    </div>

    <div x-show="pw.length > 0" style="display: none;">
        <div class="grid grid-cols-3 gap-1.5">
            <template x-for="i in 3">
                <div class="h-1.5 rounded-full" :class="score >= i + 1 ? (score >= 4 ? 'bg-green-500' : score >= 3 ? 'bg-amber-500' : 'bg-red-500') : 'bg-gray-200'"></div>
            </template>
        </div>
        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5 text-xs">
            <span class="flex items-center gap-1" :class="hasLength ? 'text-green-600' : 'text-gray-400'">
                <svg x-show="hasLength" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span x-show="!hasLength" class="w-1.5 h-1.5 rounded-full border border-gray-300"></span>
                {{ __('8+ characters') }}
            </span>
            <span class="flex items-center gap-1" :class="hasNumber ? 'text-green-600' : 'text-gray-400'">
                <svg x-show="hasNumber" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span x-show="!hasNumber" class="w-1.5 h-1.5 rounded-full border border-gray-300"></span>
                {{ __('A number') }}
            </span>
            <span class="flex items-center gap-1" :class="hasCase ? 'text-green-600' : 'text-gray-400'">
                <svg x-show="hasCase" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span x-show="!hasCase" class="w-1.5 h-1.5 rounded-full border border-gray-300"></span>
                {{ __('Upper and lower case') }}
            </span>
            <span class="flex items-center gap-1" :class="hasSymbol ? 'text-green-600' : 'text-gray-400'">
                <svg x-show="hasSymbol" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span x-show="!hasSymbol" class="w-1.5 h-1.5 rounded-full border border-gray-300"></span>
                {{ __('A symbol') }}
            </span>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
        <x-primary-button class="w-full lg:w-auto justify-center">{{ __('Update password') }}</x-primary-button>

        @if (session('status') === 'password-updated')
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
