<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Branches') }}</h2>
            <a href="{{ route('branches.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                {{ __('+ Add Branch') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Code') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Users') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($branches as $branch)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $branch->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->code }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->phone ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->users_count }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('branches.edit', $branch) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($branch->name) }}; deleteOpen = true"
                                            class="ml-3 text-red-500 hover:text-red-700 align-middle" title="{{ __('Delete') }}">
                                            <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
              </div>
            </div>
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
