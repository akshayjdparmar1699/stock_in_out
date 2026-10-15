<div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-700">{{ __('Sales and profit') }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ __('Last 7 days, in thousand') }} &#8377;</p>
        </div>
        <div class="hidden lg:flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>{{ __('Sales') }}</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>{{ __('Profit') }}</span>
        </div>
    </div>
    <div class="p-5" x-data="lineChart({
            labels: {{ \Illuminate\Support\Js::from($labels) }},
            sales: {{ \Illuminate\Support\Js::from($sales) }},
            profit: {{ \Illuminate\Support\Js::from($profit) }},
        })" x-init="render($refs.canvas)">
        <canvas x-ref="canvas" height="110"></canvas>

        <div class="lg:hidden flex items-center gap-4 text-xs text-gray-500 mt-3">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>{{ __('Sales') }}</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>{{ __('Profit') }}</span>
        </div>
    </div>
</div>

@if ($lowStockCount > 0)
    <a href="{{ route('stock.index') }}" class="lg:hidden flex items-center justify-between gap-3 bg-red-50 border border-red-100 rounded-xl px-4 py-3 shadow-sm">
        <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.25 3h.008v.008h-.008V15z" />
                </svg>
            </span>
            <div class="min-w-0">
                <div class="text-sm font-semibold text-red-700">{{ $lowStockCount }} {{ __('items low on stock') }}</div>
                <div class="text-xs text-red-400">{{ __('Stock value') }} &#8377;{{ number_format($totalStockValue, 2) }}</div>
            </div>
        </div>
        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </a>
@endif
