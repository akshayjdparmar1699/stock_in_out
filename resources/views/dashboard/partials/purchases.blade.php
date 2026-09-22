<div class="divide-y">
    @forelse ($recentPurchases as $purchase)
        <a href="{{ route('purchases.show', $purchase) }}" class="flex justify-between px-5 py-3 hover:bg-gray-50">
            <div>
                <div class="text-sm font-medium text-gray-800">{{ $purchase->purchase_number }}</div>
                <div class="text-xs text-gray-500">{{ $purchase->supplier->name }}</div>
            </div>
            <div class="text-sm text-gray-700">₹{{ number_format($purchase->total, 2) }}</div>
        </a>
    @empty
        <div class="px-5 py-6 text-sm text-gray-400">{{ __('No purchases in this period.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t" @click="onClick($event)">{{ $recentPurchases->onEachSide(0)->links() }}</div>
