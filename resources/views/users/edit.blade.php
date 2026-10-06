<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit User') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ role: '{{ old('role', $user->role) }}' }">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" x-model="role"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-400">
                            <option value="staff">{{ __('Staff (assigned to one branch)') }}</option>
                            <option value="admin">{{ __('Admin (access to all branches)') }}</option>
                        </select>
                        @if ($user->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <p class="text-xs text-gray-400 mt-1">{{ __("You can't change your own role.") }}</p>
                        @endif
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="mt-4" x-show="role === 'staff'">
                        <x-input-label for="branch_id" :value="__('Branch')" />
                        <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Select branch') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <p class="text-xs text-gray-400 mb-4">{{ __('Leave the password fields blank to keep the current password.') }}</p>

                        <x-input-label for="password" :value="__('New Password (optional)')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
