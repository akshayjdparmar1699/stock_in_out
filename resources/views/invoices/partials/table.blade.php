<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Invoice #') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Customer') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($invoices as $invoice)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('invoices.show', $invoice) }}'">
                    <td class="px-6 py-4 text-sm font-medium text-indigo-700 hover:underline">{{ $invoice->invoice_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->customer->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">₹{{ number_format($invoice->total, 2) }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-700' => $invoice->status === 'paid',
                            'bg-yellow-100 text-yellow-700' => $invoice->status === 'partial',
                            'bg-red-100 text-red-700' => $invoice->status === 'unpaid',
                        ])>{{ ucfirst($invoice->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No invoices found for this filter.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $invoices->links() }}
</div>
