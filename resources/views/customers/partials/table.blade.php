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
                    <td class="px-6 py-4 text-sm text-gray-800">
                        <a href="{{ route('customers.show', $customer) }}" class="text-indigo-700 hover:underline font-medium">{{ $customer->name }}</a>
                    </td>
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
                        <span class="inline-flex items-center gap-1 justify-end">
                            @if ($customer->isOverCreditLimit())
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="{{ __('Over credit limit') }}">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            @endif
                            ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                        </span>
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
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                @click="deleteForm = $el.closest('form'); deleteLabel = {{ \Illuminate\Support\Js::from($customer->name) }}; deleteOpen = true"
                                class="ml-3 text-red-500 hover:text-red-700 align-middle" title="{{ __('Delete') }}">
                                <svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
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
