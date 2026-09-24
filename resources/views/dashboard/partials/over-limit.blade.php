<div class="divide-y">
    @forelse ($overLimitCustomers as $row)
        <div class="flex justify-between items-center px-5 py-3">
            <div>
                <div class="text-sm text-gray-800">{{ $row['customer']->name }}</div>
                <div class="text-xs text-gray-400">{{ __('Limit') }}: ₹{{ number_format($row['customer']->credit_limit, 2) }}</div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm font-semibold text-red-600">₹{{ number_format($row['due'], 2) }}</div>
                <a href="{{ $row['whatsapp_url'] }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 hover:text-green-800 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="px-5 py-6 text-sm text-gray-400">{{ __('No customer is over their credit limit.') }}</div>
    @endforelse
</div>
<div class="px-5 py-3 border-t" @click="onClick($event)">{{ $overLimitCustomers->onEachSide(0)->links() }}</div>
