<div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <div>
        <h3 class="font-semibold text-gray-700">{{ __('Over credit limit') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Time to collect payment') }}</p>
    </div>
    <a href="{{ route('customers.index') }}" class="text-xs font-medium text-brand-600 hover:underline">{{ __('View all') }}</a>
</div>
<div class="divide-y divide-gray-50">
    @forelse ($overLimitCustomers as $row)
        @php
            $limit = (float) $row['customer']->credit_limit;
            $due = $row['due'];
            $over = max($due - $limit, 0);
            $limitPct = $due > 0 ? min(round(($limit / $due) * 100), 100) : 100;
        @endphp
        <div class="px-5 py-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-800 truncate">{{ $row['customer']->name }}</div>
                    <div class="text-xs text-gray-400 hidden lg:block">{{ __('Limit') }} &#8377;{{ number_format($limit, 0) }} &middot; <span class="text-red-500 font-medium">&#8377;{{ number_format($over, 0) }} {{ __('over') }}</span></div>
                    <div class="text-xs font-semibold text-red-600 lg:hidden">&#8377;{{ number_format($due, 2) }} &middot; &#8377;{{ number_format($over, 0) }} {{ __('over') }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm font-bold text-red-600 hidden lg:inline">&#8377;{{ number_format($due, 0) }}</span>
                    @if ($row['whatsapp_url'])
                        <a href="{{ $row['whatsapp_url'] }}" target="_blank" class="hidden lg:inline-flex items-center gap-1 text-xs font-medium text-green-700 border border-green-200 rounded-full px-2.5 py-1 hover:bg-green-50">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                            {{ __('Remind') }}
                        </a>
                        <a href="{{ $row['whatsapp_url'] }}" target="_blank" aria-label="{{ __('Remind') }}" class="lg:hidden w-9 h-9 rounded-full bg-green-50 text-green-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.001 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.373 5.07L2 22l5.06-1.348A9.94 9.94 0 0012.001 22C17.523 22 22 17.52 22 12S17.523 2 12.001 2zm0 18.062a8.02 8.02 0 01-4.253-1.213l-.305-.181-3.005.8.803-2.93-.198-.303A8.024 8.024 0 013.938 12c0-4.444 3.618-8.062 8.063-8.062S20.062 7.556 20.062 12 16.446 20.062 12.001 20.062z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
            <div class="relative h-2 rounded-full bg-gray-100 overflow-hidden mt-2.5">
                <div class="absolute inset-y-0 left-0 bg-brand-300" style="width: {{ $limitPct }}%"></div>
                <div class="absolute inset-y-0 bg-red-500" style="left: {{ $limitPct }}%; right: 0;"></div>
                <div class="absolute inset-y-0 w-0.5 bg-gray-900" style="left: {{ $limitPct }}%;"></div>
            </div>
        </div>
    @empty
        <div class="px-5 py-8 text-sm text-gray-400 text-center flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            {{ __('No customer is over their credit limit.') }}
        </div>
    @endforelse
</div>
<div class="px-5 py-3 border-t border-gray-100" @click="onClick($event)">{{ $overLimitCustomers->onEachSide(0)->links() }}</div>
