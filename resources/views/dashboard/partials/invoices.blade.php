<div class="divide-y">
    @forelse ($recentInvoices as $invoice)
        <a href="{{ route('invoices.show', $invoice) }}" class="flex justify-between px-5 py-3 hover:bg-gray-50">
            <div>
                <div class="text-sm font-medium text-gray-800">{{ $invoice->invoice_number }}</div>
                <div class="text-xs text-gray-500">{{ $invoice->customer->name }}</div>
            </div>
            <div class="text-sm text-gray-700">₹{{ number_format($invoice->total, 2) }}</div>
        </a>
    @empty
        <div class="px-5 py-6 text-sm text-gray-400">{{ __('No invoices in this period.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t" @click="onClick($event)">{{ $recentInvoices->onEachSide(0)->links() }}</div>
