<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Company') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('companies.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="company_name" :value="__('Company / Store Name')" />
                        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name')" required autofocus />
                        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                    </div>

                    <div class="border-t pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="branch_name" :value="__('First Branch Name')" />
                            <x-text-input id="branch_name" name="branch_name" type="text" class="mt-1 block w-full" :value="old('branch_name')" required />
                            <x-input-error :messages="$errors->get('branch_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="branch_code" :value="__('Branch Code (short, unique)')" />
                            <x-text-input id="branch_code" name="branch_code" type="text" class="mt-1 block w-full" :value="old('branch_code')" required />
                            <x-input-error :messages="$errors->get('branch_code')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-xs text-gray-400 mb-3">{{ __("This becomes the company's admin login — share the email and password with them.") }}</p>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="admin_name" :value="__('Admin Name')" />
                                <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name')" required />
                                <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="admin_email" :value="__('Admin Email')" />
                                <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required />
                                <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="admin_password" :value="__('Admin Password')" />
                                <x-text-input id="admin_password" name="admin_password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="admin_password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="admin_password_confirmation" name="admin_password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('companies.index') }}" class="text-sm text-gray-500 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Create Company') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
