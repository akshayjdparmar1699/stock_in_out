<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('users.index') }}" class="hover:text-gray-600">{{ __('Users') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ __('Add User') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Add User') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" form="user-form" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('Create User') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12" x-data="{ role: '{{ old('role', 'staff') }}', showPassword: false, showConfirm: false }">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <form method="POST" action="{{ route('users.store') }}" id="user-form" class="space-y-4">
                @csrf

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Role') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer" :class="role === 'admin' ? 'border-brand-600 bg-brand-50/40' : 'border-gray-200'">
                            <input type="radio" name="role" value="admin" x-model="role" class="sr-only">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0" :class="role === 'admin' ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500'">A</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ __('Admin') }}</span>
                                <span class="block text-xs text-gray-400 mt-0.5">{{ __('All branches, all modules') }}</span>
                            </span>
                        </label>
                        <label class="relative flex items-start gap-3 rounded-xl border-2 p-4 cursor-pointer" :class="role === 'staff' ? 'border-brand-600 bg-brand-50/40' : 'border-gray-200'">
                            <input type="radio" name="role" value="staff" x-model="role" class="sr-only">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0" :class="role === 'staff' ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-500'">S</span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ __('Staff') }}</span>
                                <span class="block text-xs text-gray-400 mt-0.5">{{ __('One branch only') }}</span>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />

                    <div class="mt-4" x-show="role === 'staff'" style="display: none;">
                        <x-input-label for="branch_id">{{ __('Works at branch') }} <span class="text-red-500">*</span></x-input-label>
                        <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">{{ __('Select branch') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                    </div>
                </div>

                <div class="bg-white rounded-2xl lg:shadow-sm border border-gray-100 lg:border-0 p-4 lg:p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Login details') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name">{{ __('Name') }} <span class="text-red-500">*</span></x-input-label>
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" placeholder="{{ __('e.g. Piyush Barad') }}" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email">{{ __('Email (used to log in)') }} <span class="text-red-500">*</span></x-input-label>
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('name@gmail.com') }}" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password">{{ __('Password') }} <span class="text-red-500">*</span></x-input-label>
                            <div class="relative mt-1">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password"
                                    class="block w-full pr-16 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-brand-600 hover:underline" tabindex="-1">
                                    <span x-text="showPassword ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation">{{ __('Type it again') }} <span class="text-red-500">*</span></x-input-label>
                            <div class="relative mt-1">
                                <input :type="showConfirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                    class="block w-full pr-16 border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-brand-600 hover:underline" tabindex="-1">
                                    <span x-text="showConfirm ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="user-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Create User') }}
            </button>
        </div>
    </div>
</x-app-layout>
