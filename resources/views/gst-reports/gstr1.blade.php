<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <x-application-logo class="lg:hidden w-9 h-9 shrink-0" />
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('GSTR-1 Report') }}</h2>
                    <p class="hidden lg:block text-sm text-gray-400 mt-0.5">{{ __('Outward supplies for one GST-registered branch and month') }}</p>
                </div>
            </div>
            <div class="lg:hidden flex items-center">
                @include('layouts.partials.settings-icon-mobile')
                @include('layouts.partials.notification-bell')
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if ($branches->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center">
                    <p class="text-gray-600 font-medium">{{ __('No GST-registered branch yet') }}</p>
                    <p class="text-sm text-gray-400 mt-1.5 max-w-md mx-auto">{{ __('Add a GSTIN to a branch (from Branches) to generate its GSTR-1 report.') }}</p>
                </div>
            @else
                {{-- Branch + period filters --}}
                <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                    <div class="relative flex-1">
                        <select name="branch_id" onchange="this.form.submit()"
                            class="w-full appearance-none border-gray-200 bg-gray-50 rounded-lg pl-3 pr-8 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-500 focus:ring-brand-500">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($selectedBranch?->id === $branch->id)>{{ $branch->name }} &middot; {{ $branch->gstin }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </div>

                    <div class="relative sm:w-56">
                        <select name="period" onchange="this.form.submit()"
                            class="w-full appearance-none border-gray-200 bg-gray-50 rounded-lg pl-3 pr-8 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-500 focus:ring-brand-500">
                            @foreach ($monthOptions as $opt)
                                <option value="{{ $opt['year'] }}-{{ $opt['month'] }}" @selected($year === $opt['year'] && $month === $opt['month'])>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </div>

                    @if ($selectedBranch)
                        <a href="{{ route('gst-reports.gstr1.csv', ['branch_id' => $selectedBranch->id, 'year' => $year, 'month' => $month]) }}"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            {{ __('Download CSV') }}
                        </a>
                    @endif
                </form>

                @if ($data)
                    <p class="text-xs text-gray-400 px-1">
                        {{ __('Figures are the exact GST amounts stored on each invoice at billing time — they will always match the invoice PDFs already shared. This is a review report for filing, not a direct upload to the GST portal.') }}
                    </p>

                    @if ($data['warnings']->isNotEmpty())
                        <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 space-y-1">
                            @foreach ($data['warnings'] as $warning)
                                <p>&#9888; {{ $warning }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Summary --}}
                    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
                        @foreach ([
                            [__('Invoices'), $data['summary']['invoice_count'], false],
                            [__('Taxable value'), $data['summary']['taxable_value'], true],
                            [__('CGST'), $data['summary']['cgst'], true],
                            [__('SGST'), $data['summary']['sgst'], true],
                            [__('IGST'), $data['summary']['igst'], true],
                            [__('Invoice value'), $data['summary']['invoice_value'], true],
                        ] as [$label, $value, $isMoney])
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <p class="text-xs text-gray-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ $isMoney ? '₹'.number_format($value, 0) : number_format($value) }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- B2B --}}
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ __('B2B — invoices to GSTIN-registered customers') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">{{ __('GSTIN') }}</th>
                                        <th class="px-4 py-2.5 text-left">{{ __('Customer') }}</th>
                                        <th class="px-4 py-2.5 text-left">{{ __('Invoice') }}</th>
                                        <th class="px-4 py-2.5 text-left">{{ __('Date') }}</th>
                                        <th class="px-4 py-2.5 text-left">{{ __('Place of supply') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Rate') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Taxable') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('CGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('SGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('IGST') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($data['b2b'] as $row)
                                        <tr>
                                            <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $row['gstin'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-800">{{ $row['customer_name'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $row['invoice_number'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap">{{ optional($row['invoice_date'])->format('d M') }}</td>
                                            <td class="px-4 py-2.5 text-gray-600">{{ $row['place_of_supply'] ?? '—' }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                                            <td class="px-4 py-2.5 text-right text-gray-800">{{ number_format($row['taxable_value'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['cgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['sgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['igst'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="10" class="px-4 py-6 text-center text-gray-400">{{ __('No B2B sales this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- B2CL --}}
                    @if ($data['b2cl']->isNotEmpty())
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-800 text-sm">{{ __('B2C Large — inter-state, invoice value over ₹2,50,000') }}</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">{{ __('Invoice') }}</th>
                                            <th class="px-4 py-2.5 text-left">{{ __('Date') }}</th>
                                            <th class="px-4 py-2.5 text-left">{{ __('Place of supply') }}</th>
                                            <th class="px-4 py-2.5 text-right">{{ __('Rate') }}</th>
                                            <th class="px-4 py-2.5 text-right">{{ __('Taxable') }}</th>
                                            <th class="px-4 py-2.5 text-right">{{ __('IGST') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach ($data['b2cl'] as $row)
                                            <tr>
                                                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $row['invoice_number'] }}</td>
                                                <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap">{{ optional($row['invoice_date'])->format('d M') }}</td>
                                                <td class="px-4 py-2.5 text-gray-600">{{ $row['place_of_supply'] ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-right text-gray-600">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                                                <td class="px-4 py-2.5 text-right text-gray-800">{{ number_format($row['taxable_value'], 2) }}</td>
                                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['igst'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- B2CS --}}
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ __('B2C Small — aggregated by place of supply and rate') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">{{ __('Place of supply') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Rate') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Taxable') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('CGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('SGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('IGST') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($data['b2cs'] as $row)
                                        <tr>
                                            <td class="px-4 py-2.5 text-gray-600">{{ $row['place_of_supply'] ?? '—' }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                                            <td class="px-4 py-2.5 text-right text-gray-800">{{ number_format($row['taxable_value'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['cgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['sgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['igst'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">{{ __('No B2C sales this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- HSN summary --}}
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ __('HSN-wise summary') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">{{ __('HSN') }}</th>
                                        <th class="px-4 py-2.5 text-left">{{ __('UQC') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Rate') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Quantity') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Taxable') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('CGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('SGST') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('IGST') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse ($data['hsn'] as $row)
                                        <tr>
                                            <td class="px-4 py-2.5 text-gray-800 whitespace-nowrap">{{ $row['hsn_code'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $row['uqc'] }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ rtrim(rtrim(number_format($row['quantity'], 2), '0'), '.') }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-800">{{ number_format($row['taxable_value'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['cgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['sgst'], 2) }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($row['igst'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">{{ __('No GST sales this period.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
