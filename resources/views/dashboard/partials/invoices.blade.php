<div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <div>
        <h3 class="font-semibold text-gray-700">{{ __('Recent invoices') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Latest bills from this branch') }}</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="text-xs font-medium text-brand-600 hover:underline">{{ __('View all') }}</a>
</div>
<div class="divide-y divide-gray-50">
    @forelse ($recentInvoices as $invoice)
        <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50">
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-800 truncate">
                    {{ $invoice->displayCustomerName() }}
                    @if ($invoice->isGuestCustomer())
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px]">{{ __('Unregistered') }}</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400">{{ $invoice->invoice_number }} &middot; {{ $invoice->invoice_date->format('d M') }}</div>
            </div>
            <div class="text-right shrink-0 ml-3">
                <div class="text-sm font-semibold text-gray-800">&#8377;{{ number_format($invoice->total, 2) }}</div>
                <span @class([
                    'inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-medium',
                    'bg-green-100 text-green-700' => $invoice->status === 'paid',
                    'bg-amber-100 text-amber-700' => $invoice->status === 'partial',
                    'bg-red-100 text-red-700' => $invoice->status === 'unpaid',
                ])>{{ $invoice->status === 'partial' ? __('Partly paid') : ucfirst($invoice->status) }}</span>
            </div>
        </a>
    @empty
        <div class="px-5 py-8 text-sm text-gray-400 text-center">{{ __('No invoices in this period.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t border-gray-100" @click="onClick($event)">{{ $recentInvoices->onEachSide(0)->links() }}</div>
