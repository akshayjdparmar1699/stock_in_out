{{--
    Invoice format 3: GST Tax Invoice (also renders non-GST bills with the same layout).

    Expected variables (arrays or objects, read via data_get):
      $business  name, address, phone, gstin, state_name, state_code, logo_path (absolute path),
                 bank_name, account_no, account_holder, ifsc, upi_qr (data URI or absolute URL), terms
      $invoice   number, date (Carbon|string), is_gst (bool), rates_include_gst (bool),
                 place_of_supply_name, place_of_supply_code, reverse_charge (bool),
                 payment_mode, status, received, previous_due, discount, transport, note,
                 ship_to_name, ship_to_address
      $customer  name, address, phone, gstin
      $items     iterable of: name, hsn, qty, unit, rate, gst_rate
      $forPdf    bool, true when rendered through dompdf (hides the print toolbar, uses DejaVu Sans)

    Layout uses tables only (no flex/grid) so dompdf and browser print render the same.
--}}
@php
    $forPdf = $forPdf ?? false;
    $g = fn ($src, $key, $default = null) => data_get($src, $key, $default);

    $isGst = (bool) $g($invoice, 'is_gst', true);
    $ratesIncludeGst = (bool) $g($invoice, 'rates_include_gst', false);
    $sellerState = (string) $g($business, 'state_code', '');
    $supplyState = (string) $g($invoice, 'place_of_supply_code', $sellerState);
    $isInterState = $isGst && $sellerState !== '' && $supplyState !== '' && $sellerState !== $supplyState;

    $money = fn ($v) => '₹' . number_format((float) $v, 2);
    $num = fn ($v) => number_format((float) $v, 2);
    $qtyFmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');

    $rows = [];
    $hsnSummary = [];
    $subtotal = 0.0;
    $taxTotal = 0.0;

    foreach ($items as $item) {
        $qty = (float) $g($item, 'qty', 0);
        $rate = (float) $g($item, 'rate', 0);
        $gstRate = $isGst ? (float) $g($item, 'gst_rate', 0) : 0.0;
        $line = $qty * $rate;

        // When rates include GST, back out the taxable value so totals match the billed amount.
        $taxable = ($isGst && $ratesIncludeGst && $gstRate > 0) ? $line / (1 + $gstRate / 100) : $line;
        $tax = $isGst ? round($taxable * $gstRate / 100, 2) : 0.0;
        $taxable = round($taxable, 2);

        $rows[] = [
            'name' => $g($item, 'name'),
            'hsn' => $g($item, 'hsn'),
            'qty' => $qtyFmt($qty),
            'unit' => $g($item, 'unit'),
            'rate' => $rate,
            'gst_rate' => $gstRate,
            'taxable' => $taxable,
            'half' => round($tax / 2, 2),
            'tax' => $tax,
            'total' => $taxable + $tax,
        ];

        $subtotal += $taxable;
        $taxTotal += $tax;

        if ($isGst) {
            $key = (string) ($g($item, 'hsn') ?: '-');
            $hsnSummary[$key] ??= ['taxable' => 0.0, 'tax' => 0.0];
            $hsnSummary[$key]['taxable'] += $taxable;
            $hsnSummary[$key]['tax'] += $tax;
        }
    }

    $discount = (float) $g($invoice, 'discount', 0);
    $transport = (float) $g($invoice, 'transport', 0);
    $rawTotal = $subtotal + $taxTotal - $discount + $transport;
    $grandTotal = round($rawTotal);
    $roundOff = $grandTotal - $rawTotal;
    $received = (float) $g($invoice, 'received', 0);
    $previousDue = (float) $g($invoice, 'previous_due', 0);
    $balanceDue = $previousDue + $grandTotal - $received;

    $amountInWords = \App\Support\NumberToWords::convert((int) $grandTotal) . ' Rupees Only';

    $date = $g($invoice, 'date');
    $dateText = $date instanceof \DateTimeInterface ? $date->format('d M Y') : (string) $date;

    // Embed the logo as base64 so dompdf does not need remote or chroot file access.
    $logoPath = $g($business, 'logo_path');
    $logoSrc = null;
    if ($logoPath && is_file($logoPath)) {
        $mime = mime_content_type($logoPath) ?: 'image/png';
        $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
    }
    $initials = collect(explode(' ', (string) $g($business, 'name', '')))
        ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $minRows = 8;
    $fillerHeight = max(0, $minRows - count($rows)) * 24;
    $itemColspan = $isGst ? ($isInterState ? 9 : 11) : 6;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isGst ? 'Tax Invoice' : 'Invoice' }} {{ $g($invoice, 'number') }}</title>
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
        table { border-collapse: collapse; width: 100%; }
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
<table class="frame">
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
                            <img src="{{ $logoSrc }}" alt="{{ $g($business, 'name') }} logo" style="width: 46px; height: 46px; border-radius: 12px;">
                        @else
                            <div class="logo-box">{{ $initials }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 15px; font-weight: 800;">{{ $g($business, 'name') }}</div>
                        @if ($g($business, 'address'))<div>{{ $g($business, 'address') }}</div>@endif
                        @if ($g($business, 'phone'))<div>Phone: {{ $g($business, 'phone') }}</div>@endif
                        @if ($isGst && $g($business, 'gstin'))
                            <div><b>GSTIN:</b> {{ $g($business, 'gstin') }}</div>
                            @if ($g($business, 'state_name'))<div><b>State:</b> {{ $g($business, 'state_name') }} ({{ $sellerState }})</div>@endif
                        @endif
                    </td>
                </tr></table>
            </td>
            <td style="width: 44%; padding: 0;">
                <table>
                    <tr>
                        <td class="vr" style="padding: 7px 10px; border-bottom: 1px solid #111111; width: 50%;"><div class="lb">Invoice No.</div><b>{{ $g($invoice, 'number') }}</b></td>
                        <td style="padding: 7px 10px; border-bottom: 1px solid #111111;"><div class="lb">Date</div><b>{{ $dateText }}</b></td>
                    </tr>
                    <tr>
                        @if ($isGst)
                            <td class="vr" style="padding: 7px 10px;"><div class="lb">Place of Supply</div><b>{{ $g($invoice, 'place_of_supply_name', $g($business, 'state_name')) }} ({{ $supplyState }})</b></td>
                            <td style="padding: 7px 10px;"><div class="lb">Reverse Charge</div><b>{{ $g($invoice, 'reverse_charge') ? 'Yes' : 'No' }}</b></td>
                        @else
                            <td class="vr" style="padding: 7px 10px;"><div class="lb">Payment Mode</div><b>{{ $g($invoice, 'payment_mode', '-') }}</b></td>
                            <td style="padding: 7px 10px;"><div class="lb">Status</div><b>{{ $g($invoice, 'status', '-') }}</b></td>
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
                <div class="big">{{ $g($customer, 'name') }}</div>
                @if ($g($customer, 'address'))<div>{{ $g($customer, 'address') }}</div>@endif
                @if ($g($customer, 'phone'))<div>{{ $g($customer, 'phone') }}</div>@endif
                @if ($isGst && $g($customer, 'gstin'))<div><b>GSTIN:</b> {{ $g($customer, 'gstin') }}</div>@endif
            </td>
            <td class="pad">
                <div class="lb">Ship To</div>
                <div class="big">{{ $g($invoice, 'ship_to_name', $g($customer, 'name')) }}</div>
                <div>{{ $g($invoice, 'ship_to_address') ?: 'Same as billing address' }}</div>
            </td>
        </tr></table>
    </td></tr>

    {{-- Items --}}
    <tr><td style="padding: 0;">
        <table class="grid" style="margin-top: -1px;">
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
    </td></tr>

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
                @elseif ($g($invoice, 'note'))
                    <div class="lb">Note</div>
                    <div>{{ $g($invoice, 'note') }}</div>
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
                    @endif
                    @if ($discount != 0)<tr><td>Discount</td><td class="r">- {{ $money($discount) }}</td></tr>@endif
                    @if ($transport != 0)<tr><td>Transport</td><td class="r">{{ $money($transport) }}</td></tr>@endif
                    @if (abs($roundOff) >= 0.005)<tr><td>Round off</td><td class="r">{{ $money($roundOff) }}</td></tr>@endif
                    <tr class="grand"><td>Invoice total</td><td class="r">{{ $money($grandTotal) }}</td></tr>
                    <tr><td>Received</td><td class="r">{{ $money($received) }}</td></tr>
                    <tr><td>Previous due</td><td class="r">{{ $money($previousDue) }}</td></tr>
                    <tr><td><b>Balance due</b></td><td class="r"><b style="color: {{ $balanceDue > 0 ? '#B42318' : '#12804A' }};">{{ $money($balanceDue) }}</b></td></tr>
                </table>
            </td>
        </tr></table>
    </td></tr>

    {{-- Payment details, terms and signature --}}
    <tr><td>
        <table><tr>
            <td class="vr pad" style="width: 56%;">
                <table><tr>
                    @if ($g($business, 'upi_qr'))
                        <td style="width: 100px;">
                            <img src="{{ $g($business, 'upi_qr') }}" alt="UPI payment QR code" style="width: 88px; height: 88px;">
                            <div class="lb" style="text-align: center; margin-top: 2px;">Scan &amp; Pay</div>
                        </td>
                    @endif
                    <td>
                        @if ($g($business, 'account_no'))
                            <div class="lb">Bank details</div>
                            <div><b>{{ $g($business, 'bank_name') }}</b></div>
                            <div>A/c {{ $g($business, 'account_no') }}</div>
                            @if ($g($business, 'account_holder'))<div>{{ $g($business, 'account_holder') }}</div>@endif
                            @if ($g($business, 'ifsc'))<div>IFSC {{ $g($business, 'ifsc') }}</div>@endif
                        @endif
                        @if ($g($business, 'terms'))
                            <div class="lb" style="margin-top: 8px;">Terms</div>
                            <div>{!! nl2br(e($g($business, 'terms'))) !!}</div>
                        @endif
                    </td>
                </tr></table>
            </td>
            <td class="pad r" style="height: 130px;">
                <div><b>For {{ $g($business, 'name') }}</b></div>
                <div style="height: 64px;"></div>
                <div style="border-top: 1px solid #111111; display: inline-block; padding-top: 4px; min-width: 150px; text-align: center;">Authorised signatory</div>
            </td>
        </tr></table>
    </td></tr>
</table>
</div>
</body>
</html>
