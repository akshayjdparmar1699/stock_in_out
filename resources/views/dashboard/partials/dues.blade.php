@php
    $customerDue = max($customerDueTotal, 0);
    $supplierDue = max($supplierDueTotal, 0);
    $maxDue = max($customerDue, $supplierDue, 1);
    $net = $customerDueTotal - $supplierDueTotal;
@endphp
<div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <h3 class="font-semibold text-gray-700">{{ __('Money position') }}</h3>
    <span class="text-xs text-gray-400 lg:hidden">{{ __('Udhaar') }}</span>
    <p class="hidden lg:block text-xs text-gray-400">{{ __('Udhaar you will receive vs pay') }}</p>
</div>
<div class="p-5 space-y-4">
    <div>
        <div class="flex items-center justify-between text-sm mb-1.5">
            <span class="text-gray-600">{{ __('Customers owe you') }}</span>
            <span class="font-semibold text-green-600">&#8377;{{ number_format($customerDue, 2) }}</span>
        </div>
        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
            <div class="h-full bg-green-500 rounded-full" style="width: {{ $customerDue > 0 ? round(($customerDue / $maxDue) * 100) : 0 }}%"></div>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between text-sm mb-1.5">
            <span class="text-gray-600">{{ __('You owe suppliers') }}</span>
            <span class="font-semibold text-red-600">&#8377;{{ number_format($supplierDue, 2) }}</span>
        </div>
        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
            <div class="h-full bg-red-500 rounded-full" style="width: {{ $supplierDue > 0 ? round(($supplierDue / $maxDue) * 100) : 0 }}%"></div>
        </div>
    </div>

    <div class="flex items-center justify-between bg-brand-50 rounded-lg px-4 py-3 mt-2">
        <div>
            <div class="text-xs text-gray-500">{{ $net >= 0 ? __('Net you will receive') : __('Net you will pay') }}</div>
            <div class="text-lg font-bold {{ $net >= 0 ? 'text-brand-700' : 'text-red-600' }} mt-0.5">&#8377;{{ number_format(abs($net), 2) }}</div>
        </div>
        <div class="text-right">
            <div class="text-xs text-gray-500"><span class="lg:hidden">{{ __('Advance') }}</span><span class="hidden lg:inline">{{ __('Staff + Partner Advance') }}</span></div>
            <div class="text-sm font-semibold text-gray-700 mt-0.5">&#8377;{{ number_format($totalUpad, 2) }}</div>
        </div>
    </div>
</div>
