<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Address') }}</th>
                @if (Auth::user()->isAdmin())
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branches') }}</th>
                @endif
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Balance Due') }}</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($customers as $customer)
                @php $due = $customer->dueAmount(); @endphp
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $customer->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $customer->phone }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $customer->address ?? '—' }}</td>
                    @if (Auth::user()->isAdmin())
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($customer->branches as $branch)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $branch->name }}</span>
                                @endforeach
                            </div>
                        </td>
                    @endif
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-400') }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="{{ route('customers.toggle-active', $customer) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                data-name="{{ $customer->name }}"
                                data-active="{{ $customer->is_active ? '1' : '0' }}"
                                @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                title="{{ $customer->is_active ? __('Click to mark inactive') : __('Click to mark active') }}"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $customer->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No customers found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $customers->links() }}
</div>
