<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Leads') }}</h2>
    </x-slot>

    <div class="py-12">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Message') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Received') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($leads as $lead)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ $lead->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $lead->shop_name ?? '—' }}</td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No inquiries yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $leads->links() }}
        </div>
    </div>
</x-app-layout>
