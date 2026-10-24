{{--
    Invoice format: GST Tax Invoice (also renders non-GST bills with the
    same layout — only the GST-specific parts are conditional).

    $invoice is the real Eloquent model, eager-loaded with
    customer/branch.company/items.item/payments by InvoiceController::pdf().
    $customerDue is $invoice->customer?->dueAmount(), passed in by the
    controller so this view never has to re-query it.

    $forPdf defaults true: this view is only ever rendered through dompdf
    today (Print/Share/Download all stream the same PDF via the
    invoices.pdf route). The forPdf=false branch (web toolbar, Google
    Font) is kept so a future on-screen "print preview" page can reuse
    this exact template unchanged by rendering it with forPdf=false.
--}}
@php
    $forPdf = $forPdf ?? true;

    $branch = $invoice->branch;
    $company = $branch->company;
    $customer = $invoice->customer;
    $isGst = (bool) $invoice->is_gst_invoice;

    $branchStateCode = \App\Support\GstStateCodes::code($branch->state) ?: $branch->state_code;
    $customerStateCode = $customer ? (\App\Support\GstStateCodes::code($customer->state) ?: $customer->state_code) : null;

    // Same state (branch vs customer) means CGST+SGST was charged;
    // different states mean IGST was charged instead — decided once at
    // billing time and snapshotted onto each line (applyLines() leaves
    // cgst_amount null for an inter-state line, igst_amount null for an
    // intra-state one). Re-derived from that snapshot, not from the
    // customer's current address, so an address edited later never
    // repaints an already-issued invoice's tax split.
    $isSameState = $invoice->items->contains(fn ($line) => $line->cgst_amount !== null);
    $isInterState = $isGst && ! $isSameState;

    $money = fn ($v) => '₹'.number_format((float) $v, 2);
    $num = fn ($v) => number_format((float) $v, 2);

    $rows = [];
    $hsnSummary = [];
    $subtotal = 0.0;
    $taxTotal = 0.0;

    foreach ($invoice->items as $line) {
        // The line's own total/gst_rate are already frozen at the rate
        // charged when this invoice was created, so recomputing CGST/SGST
        // from them here reproduces the exact figures stored on the line
        // — without a later edit to the item's own GST rate ever being
        // able to repaint a past invoice.
        $taxable = (float) $line->total;
        $gstRate = $isGst ? (float) ($line->gst_rate ?? 0) : 0.0;
        $tax = $isGst ? round($taxable * $gstRate / 100, 2) : 0.0;
        $half = round($tax / 2, 2);

        $rows[] = [
            'name' => $line->item->name,
            'hsn' => $line->hsn_code,
            'qty' => rtrim(rtrim((string) $line->quantity, '0'), '.'),
            'unit' => $line->displayUnit(),
            'rate' => (float) $line->unit_price,
            'gst_rate' => $gstRate,
            'taxable' => $taxable,
            'half' => $half,
            'tax' => $tax,
            'total' => $taxable + $tax,
        ];

        $subtotal += $taxable;
        $taxTotal += $tax;

        if ($isGst) {
            $key = $line->hsn_code ?: '-';
            $hsnSummary[$key] ??= ['taxable' => 0.0, 'tax' => 0.0];
            $hsnSummary[$key]['taxable'] += $taxable;
            $hsnSummary[$key]['tax'] += $tax;
        }
    }

    $discount = (float) $invoice->discount;
    $transport = (float) $invoice->transportation;
    // Non-GST bills can still carry the older flat "tax" amount entered at
    // billing time; GST bills compute their tax per line above instead.
    $flatTax = $isGst ? 0.0 : (float) $invoice->tax;
    $rawTotal = $subtotal + $taxTotal + $flatTax - $discount + $transport;

    // GST bills round the final total to the nearest rupee (a standard GST
    // filing convention); non-GST bills keep paise precision so this
    // always matches the figure already stored on the invoice exactly.
    $grandTotal = $isGst ? round($rawTotal) : round($rawTotal, 2);
    $roundOff = $isGst ? round($grandTotal - $rawTotal, 2) : 0.0;

    $received = (float) $invoice->paid_amount;

    if ($invoice->isGuestCustomer()) {
        $previousDue = 0.0;
        $balanceDue = $invoice->balanceDue();
    } else {
        $previousDue = (float) $customerDue - ($invoice->total - $invoice->paid_amount);
        $balanceDue = (float) $customerDue;
    }

    // The payment mode captured at billing time (if any) — an invoice can
    // gain further payments later with their own modes, but this is the
    // one that best represents "how this bill was paid".
    $billingPayment = $invoice->payments->firstWhere('note', 'Paid at billing');
    $paymentModeLabel = match ($billingPayment?->mode) {
        'cash' => 'Cash',
        'upi' => 'UPI',
        'bank' => 'Bank',
        default => null,
    };
    $statusLabel = match ($invoice->status) {
        'paid' => 'Paid',
        'partial' => 'Partly paid',
        'unpaid' => 'Unpaid',
        default => ucfirst($invoice->status),
    };

    $amountInWords = ($grandTotal > 0 ? \App\Support\NumberToWords::convert((int) $grandTotal) : 'Zero').' Rupees Only';

    $logoPath = public_path('icons/icon-192.png');
    $logoSrc = is_file($logoPath)
        ? 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode(file_get_contents($logoPath))
        : null;

    $minRows = 8;
    $fillerHeight = max(0, $minRows - count($rows)) * 24;
    $itemColspan = $isGst ? ($isInterState ? 9 : 11) : 6;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isGst ? 'Tax Invoice' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
    @unless ($forPdf)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
    @endunless
    <style>
        :root {
            --ink: #111111;
            --muted: #4A4458;
            --line: #9C97A8;
            --head-bg: #EEEDF2;
            --brand: #6A00D9;
            --accent: #FFB020;
        }
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: {{ $forPdf ? '#FFFFFF' : '#F5F4F8' }};
            color: #111111;
            font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'Figtree', 'DejaVu Sans', Arial, sans-serif" !!};
            font-size: 11px;
            line-height: 1.45;
        }
        .toolbar { max-width: 794px; margin: 16px auto 0; text-align: right; }
        .toolbar button {
            height: 40px; padding: 0 18px; border: 0; border-radius: 10px;
            background: #6A00D9; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer;
        }
        .sheet {
            width: 100%; max-width: 794px; margin: 16px auto; background: #FFFFFF;
            padding: {{ $forPdf ? '0' : '28px' }};
        }
        .frame { border: 1.5px solid #111111; width: 100%; border-collapse: collapse; }
        .frame-top { border-bottom: 0; }
        .frame-bottom { border-top: 0; }
        .items-grid { border-left: 1.5px solid #111111; border-right: 1.5px solid #111111; }
        table { border-collapse: collapse; width: 100%; margin: 0; }
        td, th { vertical-align: top; }
        .sec { border-bottom: 1.5px solid #111111; }
        .vr { border-right: 1px solid #111111; }
        .pad { padding: 10px 12px; }
        .lb { font-size: 9px; font-weight: 700; color: #4A4458; letter-spacing: 0.6px; text-transform: uppercase; }
        .big { font-size: 13px; font-weight: 700; }
        .r { text-align: right; }
        .c { text-align: center; }
        .grid td, .grid th { border: 1px solid #9C97A8; padding: 5px 6px; }
        .grid th { background: #EEEDF2; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #2A2238; }
        .grid thead { display: table-header-group; }
        .grid tr { page-break-inside: avoid; }
        .tot td { padding: 2px 0; }
        .grand td { font-size: 14px; font-weight: 700; border-top: 1px solid #111111; padding-top: 5px; }
        .logo-box {
            width: 46px; height: 46px; border-radius: 12px; background: #6A00D9; color: #FFFFFF;
            font-size: 16px; font-weight: 700; text-align: center; line-height: 46px;
        }
        @media print {
            body { background: #FFFFFF; }
            .toolbar { display: none; }
            .sheet { margin: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
@unless ($forPdf)
    <div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>
@endunless

<div class="sheet">
{{--
    Split into three stacked tables (header block / items / totals block)
    instead of one giant outer table, because dompdf will not paginate a
    table nested inside another table's row — on a long invoice it just
    pushes that whole outer row to the next page, leaving the previous
    page mostly blank. As independent top-level tables, the items table
    flows and paginates on its own.
--}}
<table class="frame frame-top">
    {{-- Title bar --}}
    <tr><td class="sec" style="padding: 9px 12px;">
        <table><tr>
            <td style="font-size: 17px; font-weight: 800; letter-spacing: 2px;">{{ $isGst ? 'TAX INVOICE' : 'INVOICE' }}</td>
            <td class="r"><span style="font-size: 9px; font-weight: 700; border: 1px solid #111111; padding: 3px 8px;">ORIGINAL FOR RECIPIENT</span></td>
        </tr></table>
    </td></tr>

    {{-- Seller and invoice meta --}}
    <tr><td class="sec">
        <table><tr>
            <td class="vr pad" style="width: 56%;">
                <table><tr>
                    <td style="width: 56px;">
                        @if ($logoSrc)
                            <img src="{{ $logoSrc }}" alt="{{ $branch->name }} logo" style="width: 46px; height: 46px; border-radius: 12px;">
                        @else
                            <div class="logo-box">{{ mb_strtoupper(mb_substr($branch->name, 0, 1)) }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 15px; font-weight: 800;">{{ $branch->name }}</div>
                        @if ($branch->address)<div>{{ $branch->address }}</div>@endif
                        @if ($branch->phone)<div>Phone: {{ $branch->phone }}</div>@endif
                        @if ($isGst && $branch->gstin)
                            <div><b>GSTIN:</b> {{ $branch->gstin }}</div>
                            @if ($branch->state)<div><b>State:</b> {{ $branch->state }}@if ($branchStateCode) ({{ $branchStateCode }})@endif</div>@endif
                        @endif
                    </td>
                </tr></table>
            </td>
            <td style="width: 44%; padding: 0;">
                <table>
                    <tr>
                        <td class="vr" style="padding: 7px 10px; border-bottom: 1px solid #111111; width: 50%;"><div class="lb">Invoice No.</div><b>{{ $invoice->invoice_number }}</b></td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #111111;"><div class="lb">Date</div><b>{{ $invoice->invoice_date->format('d M Y') }}</b></td>
                    </tr>
                    <tr>
                        @if ($isGst)
                            <td class="vr" style="padding: 7px 10px;"><div class="lb">Place of Supply</div><b>{{ $customer?->state ?: $branch->state }}@if ($customerStateCode ?? $branchStateCode) ({{ $customerStateCode ?? $branchStateCode }})@endif</b></td>
                            <td style="padding: 7px 10px;"><div class="lb">Reverse Charge</div><b>No</b></td>
                        @else
                            <td class="vr" style="padding: 7px 10px;"><div class="lb">Payment Mode</div><b>{{ $paymentModeLabel ?: '-' }}</b></td>
                            <td style="padding: 7px 10px;"><div class="lb">Status</div><b>{{ $statusLabel }}</b></td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr></table>
    </td></tr>

    {{-- Bill to / Ship to --}}
    <tr><td class="sec">
        <table><tr>
            <td class="vr pad" style="width: 50%;">
                <div class="lb">Bill To</div>
                <div class="big">{{ $invoice->displayCustomerName() }}</div>
                @if ($customer?->address)<div>{{ $customer->address }}</div>@endif
                @if ($customer?->phone)<div>{{ $customer->phone }}</div>@endif
                @if ($isGst && $customer?->gst_number)<div><b>GSTIN:</b> {{ $customer->gst_number }}</div>@endif
            </td>
            <td class="pad">
                <div class="lb">Ship To</div>
                <div class="big">{{ $invoice->displayCustomerName() }}</div>
                <div>Same as billing address</div>
                @if ($invoice->transport_note)<div style="margin-top: 4px;"><b>Transport:</b> {{ $invoice->transport_note }}</div>@endif
            </td>
        </tr></table>
    </td></tr>
</table>

    {{-- Items --}}
    <table class="grid items-grid">
            <thead>
            @if ($isGst)
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2" style="text-align: left;">Item</th>
                    <th rowspan="2">HSN</th>
                    <th rowspan="2">Qty</th>
                    <th rowspan="2">Rate</th>
                    <th rowspan="2">Taxable</th>
                    @if ($isInterState)
                        <th colspan="2">IGST</th>
                    @else
                        <th colspan="2">CGST</th>
                        <th colspan="2">SGST</th>
                    @endif
                    <th rowspan="2">Total</th>
                </tr>
                <tr>
                    <th>%</th><th>Amt</th>
                    @unless ($isInterState)<th>%</th><th>Amt</th>@endunless
                </tr>
            @else
                <tr>
                    <th>#</th>
                    <th style="text-align: left;">Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            @endif
            </thead>
            <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    @if ($isGst)
                        <td class="c">{{ $row['hsn'] ?: '-' }}</td>
                        <td class="r">{{ $row['qty'] }} {{ $row['unit'] }}</td>
                        <td class="r">{{ $num($row['rate']) }}</td>
                        <td class="r">{{ $num($row['taxable']) }}</td>
                        @if ($isInterState)
                            <td class="c">{{ rtrim(rtrim(number_format($row['gst_rate'], 2), '0'), '.') }}</td>
                            <td class="r">{{ $num($row['tax']) }}</td>
                        @else
                            <td class="c">{{ rtrim(rtrim(number_format($row['gst_rate'] / 2, 2), '0'), '.') }}</td>
                            <td class="r">{{ $num($row['half']) }}</td>
                            <td class="c">{{ rtrim(rtrim(number_format($row['gst_rate'] / 2, 2), '0'), '.') }}</td>
                            <td class="r">{{ $num($row['tax'] - $row['half']) }}</td>
                        @endif
                        <td class="r"><b>{{ $num($row['total']) }}</b></td>
                    @else
                        <td class="r">{{ $row['qty'] }}</td>
                        <td class="c">{{ $row['unit'] }}</td>
                        <td class="r">{{ $num($row['rate']) }}</td>
                        <td class="r"><b>{{ $num($row['taxable']) }}</b></td>
                    @endif
                </tr>
            @endforeach

            @if ($fillerHeight > 0)
                <tr style="height: {{ $fillerHeight }}px;">
                    @for ($k = 0; $k < $itemColspan; $k++)<td></td>@endfor
                </tr>
            @endif

            <tr style="background: #EEEDF2; font-weight: 700;">
                <td></td>
                <td>Total</td>
                @if ($isGst)
                    <td></td><td></td><td></td>
                    <td class="r">{{ $num($subtotal) }}</td>
                    @if ($isInterState)
                        <td></td><td class="r">{{ $num($taxTotal) }}</td>
                    @else
                        <td></td><td class="r">{{ $num(collect($rows)->sum('half')) }}</td>
                        <td></td><td class="r">{{ $num($taxTotal - collect($rows)->sum('half')) }}</td>
                    @endif
                    <td class="r">{{ $money($subtotal + $taxTotal) }}</td>
                @else
                    <td></td><td></td><td></td>
                    <td class="r">{{ $money($subtotal) }}</td>
                @endif
            </tr>
            </tbody>
    </table>

<table class="frame frame-bottom">
    {{-- Words, HSN summary or note, and totals --}}
    <tr><td class="sec">
        <table><tr>
            <td class="vr pad" style="width: 56%;">
                <div class="lb">Amount in words</div>
                <div style="font-weight: 700; margin-bottom: 8px;">{{ $amountInWords }}</div>

                @if ($isGst && count($hsnSummary))
                    <table class="grid" style="font-size: 9px;">
                        <thead>
                        <tr>
                            <th>HSN</th><th>Taxable</th>
                            @if ($isInterState)<th>IGST</th>@else<th>CGST</th><th>SGST</th>@endif
                            <th>Total tax</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($hsnSummary as $hsn => $sum)
                            <tr>
                                <td class="c">{{ $hsn }}</td>
                                <td class="r">{{ $num($sum['taxable']) }}</td>
                                @if ($isInterState)
                                    <td class="r">{{ $num($sum['tax']) }}</td>
                                @else
                                    <td class="r">{{ $num($sum['tax'] / 2) }}</td>
                                    <td class="r">{{ $num($sum['tax'] / 2) }}</td>
                                @endif
                                <td class="r">{{ $num($sum['tax']) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @elseif ($invoice->notes)
                    <div class="lb">Note</div>
                    <div>{{ $invoice->notes }}</div>
                @endif
            </td>
            <td class="pad">
                <table class="tot">
                    <tr><td>{{ $isGst ? 'Taxable value' : 'Subtotal' }}</td><td class="r">{{ $money($subtotal) }}</td></tr>
                    @if ($isGst)
                        @if ($isInterState)
                            <tr><td>IGST</td><td class="r">{{ $money($taxTotal) }}</td></tr>
                        @else
                            <tr><td>CGST</td><td class="r">{{ $money($taxTotal / 2) }}</td></tr>
                            <tr><td>SGST</td><td class="r">{{ $money($taxTotal / 2) }}</td></tr>
                        @endif
                    @elseif ($flatTax != 0)
                        <tr><td>Tax</td><td class="r">{{ $money($flatTax) }}</td></tr>
                    @endif
                    @if ($discount != 0)<tr><td>Discount</td><td class="r">- {{ $money($discount) }}</td></tr>@endif
                    @if ($transport != 0)<tr><td>Transport</td><td class="r">{{ $money($transport) }}</td></tr>@endif
                    @if ($isGst && $roundOff != 0)<tr><td>Round off</td><td class="r">{{ $roundOff > 0 ? '+' : '' }}{{ $money($roundOff) }}</td></tr>@endif
                    <tr class="grand"><td>Invoice total</td><td class="r">{{ $money($grandTotal) }}</td></tr>
                    <tr><td>Received</td><td class="r">{{ $money($received) }}</td></tr>
                    @if ($previousDue != 0)<tr><td>Previous due</td><td class="r">{{ $money(abs($previousDue)) }}{{ $previousDue < 0 ? ' CR' : '' }}</td></tr>@endif
                    <tr><td><b>{{ $balanceDue > 0 ? 'Balance due' : 'Settled / In credit' }}</b></td><td class="r"><b style="color: {{ $balanceDue > 0 ? '#B42318' : '#12804A' }};">{{ $money(abs($balanceDue)) }}{{ $balanceDue < 0 ? ' CR' : '' }}</b></td></tr>
                </table>
            </td>
        </tr></table>
    </td></tr>

    @if ($isGst && $invoice->notes)
        <tr><td class="sec pad">
            <div class="lb">Notes</div>
            <div>{{ $invoice->notes }}</div>
        </td></tr>
    @endif

    {{-- Payment details, terms and signature --}}
    <tr><td>
        <table><tr>
            <td class="vr pad" style="width: 56%;">
                <table><tr>
                    @if ($branch->hasPaymentDetails())
                        <td style="width: 100px;">
                            <img src="{{ 'data:'.$branch->paymentQrMimeType().';base64,'.base64_encode(file_get_contents($branch->paymentQrPath())) }}" alt="UPI payment QR code" style="width: 88px; height: 88px;">
                            <div class="lb" style="text-align: center; margin-top: 2px;">Scan &amp; Pay</div>
                        </td>
                    @endif
                    <td>
                        @if ($branch->hasPaymentDetails())
                            <div class="lb">Bank details</div>
                            <div><b>{{ $branch->paymentBankName() }}</b></div>
                            <div>A/c {{ $branch->paymentAccountNumber() }}</div>
                            <div>{{ $branch->paymentAccountHolderName() }}</div>
                            <div>IFSC {{ $branch->paymentIfscCode() }}</div>
                        @endif

                        @if ($company?->invoice_terms)
                            <div class="lb" style="margin-top: 8px;">Terms</div>
                            <div>{!! nl2br(e($company->invoice_terms)) !!}</div>
                        @else
                            <div class="lb" style="margin-top: 8px;">Terms</div>
                            <div>Certified that the particulars given above are true and correct. This is a computer-generated {{ $isGst ? 'tax invoice' : 'invoice' }}.</div>
                        @endif
                    </td>
                </tr></table>
            </td>
            <td class="pad r" style="height: 130px;">
                <div><b>For {{ $branch->name }}</b></div>
                <div style="height: 64px;"></div>
                <div style="border-top: 1px solid #111111; display: inline-block; padding-top: 4px; min-width: 150px; text-align: center;">Authorised signatory</div>
            </td>
        </tr></table>
    </td></tr>
</table>
</div>
</body>
</html>
