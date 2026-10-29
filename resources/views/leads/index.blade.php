<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Leads') }}</h2>
    </x-slot>

    <div class="py-12" x-data="{ deleteOpen: false, deleteForm: null, deleteLabel: '' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Business') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Message') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Received') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($leads as $lead)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ $lead->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lead->shop_name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lead->business_type ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <a href="https://wa.me/91{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank" class="text-brand-600 hover:underline">{{ $lead->phone }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $lead->message }}">{{ $lead->message ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ $lead->created_at->format('d M, h:i A') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <form method="POST" action="{{ route('leads.contacted', $lead) }}">
                                            @csrf
                                            @method('patch')
                                            <button type="submit" @class([
                                                'px-2 py-0.5 rounded-full text-xs font-medium',
                                                'bg-green-100 text-green-700' => $lead->contacted,
                                                'bg-amber-100 text-amber-700' => ! $lead->contacted,
                                            ])>{{ $lead->contacted ? __('Contacted') : __('New') }}</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($lead->name) }}; deleteOpen = true"
                                                title="{{ __('Delete') }}" class="w-8 h-8 rounded-lg text-red-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No inquiries yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $leads->links() }}
        </div>

        @include('partials.delete-confirm-modal')
    </div>
</x-app-layout>
