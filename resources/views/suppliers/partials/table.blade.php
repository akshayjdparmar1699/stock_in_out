<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Address') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('We Owe Them') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($suppliers as $supplier)
                @php $due = $supplier->dueAmount(); @endphp
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $supplier->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->address ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-400') }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No suppliers found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $suppliers->links() }}
</div>
