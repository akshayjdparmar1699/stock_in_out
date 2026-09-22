<div class="divide-y">
    @forelse ($lowStockItems as $stock)
        <div class="flex justify-between px-5 py-3">
            <div class="text-sm text-gray-800">{{ $stock->item->name }}</div>
            <div class="text-sm text-red-600">{{ $stock->quantity }} {{ $stock->item->unit }}</div>
        </div>
    @empty
        <div class="px-5 py-6 text-sm text-gray-400">{{ __('Everything is well stocked.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t" @click="onClick($event)">{{ $lowStockItems->onEachSide(0)->links() }}</div>
