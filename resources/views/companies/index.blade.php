<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Companies') }}</h2>
            <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ Add Company') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branches') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Users') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($companies as $company)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $company->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $company->branches_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $company->users_count }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-700' => $company->is_active,
                                        'bg-gray-100 text-gray-600' => ! $company->is_active,
                                    ])>{{ $company->is_active ? __('Active') : __('Inactive') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No companies yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
              </div>
            </div>
        </div>
    </div>
</x-app-layout>
