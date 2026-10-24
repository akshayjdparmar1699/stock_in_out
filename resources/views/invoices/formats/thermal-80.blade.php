{{--
    Invoice format: Thermal 80mm. Receipt layout for 80mm counter printers (72mm printable width).
    Renders GST and non-GST bills. For dompdf use InvoiceFormat::paperFor() so the page height
    grows with the number of items instead of splitting the receipt.
--}}
@php
    $p = new \App\Support\InvoicePresenter($business, $invoice, $customer, $items);
    $forPdf = $forPdf ?? false;
    $m = fn ($v) => \App\Support\InvoicePresenter::money($v);
    $n = fn ($v) => \App\Support\InvoicePresenter::num($v);

    $totalQty = array_sum(array_map(fn ($r) => (float) $r['qty'], $p->rows));

    $rateSummary = [];
    if ($p->isGst) {
        foreach ($p->rows as $row) {
            $key = $row['gst_label'];
            $rateSummary[$key] ??= ['taxable' => 0.0, 'tax' => 0.0];
            $rateSummary[$key]['taxable'] += $row['taxable'];
            $rateSummary[$key]['tax'] += $row['tax'];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p->title() }} {{ $p->inv('number') }}</title>
@unless ($forPdf)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">
@endunless
<style>
    @page { size: 80mm 297mm; margin: 0; }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: {{ $forPdf ? '#FFFFFF' : '#E9E9EC' }};
        color: #000000;
        font-size: {{ $forPdf ? '9px' : '11.5px' }};
        line-height: 1.4;
        font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'Roboto Mono', 'Courier New', monospace" !!};
    }
    table { border-collapse: collapse; width: 100%; }
    td { vertical-align: top; padding: 0; }
    .slip { width: 80mm; margin: {{ $forPdf ? '0' : '16px auto' }}; background: #FFFFFF; padding: 4mm 4mm 6mm; }
    .c { text-align: center; }
    .r { text-align: right; white-space: nowrap; }
    .b { font-weight: 700; }
    .sm { font-size: 0.85em; }
    .cut { border-top: 1.5px dashed #000000; margin: 5px 0; }
    .solid { border-top: 1.5px solid #000000; margin: 5px 0; }
    .item { padding: 3px 0; }
    .grand td { font-size: 1.35em; font-weight: 700; padding: 3px 0; }
    .toolbar { width: 80mm; margin: 16px auto 0; text-align: right; }
    .toolbar button { height: 36px; padding: 0 16px; border: 0; border-radius: 8px; background: #111111; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer; }
    @media print {
        body { background: #FFFFFF; }
        .toolbar { display: none; }
        .slip { margin: 0; width: 100%; }
    }
</style>
</head>
<body>
@unless ($forPdf)<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>@endunless
<div class="slip">

    <div class="c">
        @if ($p->logoSrc)
            <img src="{{ $p->logoSrc }}" alt="{{ $p->biz('name') }} logo" style="width: 34px; height: 34px; margin-bottom: 2px; filter: grayscale(100%);">
        @endif
        <div class="b" style="font-size: 1.3em; text-transform: uppercase;">{{ $p->biz('name') }}</div>
        @if ($p->biz('address'))<div>{{ $p->biz('address') }}</div>@endif
        @if ($p->biz('phone'))<div>Ph: {{ $p->biz('phone') }}</div>@endif
        @if ($p->isGst && $p->biz('gstin'))<div>GSTIN: {{ $p->biz('gstin') }}</div>@endif
        <div class="b" style="margin-top: 4px; letter-spacing: 1px; text-transform: uppercase;">{{ $p->title() }}</div>
    </div>

    <div class="cut"></div>
    <table>
        <tr><td>Bill: <span class="b">{{ $p->inv('number') }}</span></td><td class="r">{{ $p->dateText }}</td></tr>
        <tr><td colspan="2">To: <span class="b">{{ $p->cust('name') }}</span>@if ($p->cust('phone')) ({{ $p->cust('phone') }})@endif</td></tr>
        @if ($p->isGst && $p->cust('gstin'))<tr><td colspan="2">GSTIN: {{ $p->cust('gstin') }}</td></tr>@endif
        @if ($p->isGst)<tr><td colspan="2">Supply: {{ $p->inv('place_of_supply_name', $p->biz('state_name')) }} ({{ $p->supplyState }})</td></tr>@endif
    </table>

    <div class="cut"></div>
    <table><tr class="b"><td>ITEM</td><td class="r">AMOUNT</td></tr></table>
    <div class="cut" style="margin: 3px 0;"></div>

    @foreach ($p->rows as $row)
        <div class="item">
            <div class="b">{{ $row['name'] }}</div>
            <table><tr>
                <td>{{ $row['qty'] }} {{ $row['unit'] }} x {{ $n($row['rate']) }}</td>
                <td class="r b">{{ $n($row['total']) }}</td>
            </tr></table>
            @if ($p->isGst)
                <div class="sm">@if ($row['hsn'])HSN {{ $row['hsn'] }} | @endif GST {{ $row['gst_label'] }} = {{ $n($row['tax']) }}</div>
            @endif
        </div>
    @endforeach

    <div class="cut"></div>
    <table>
        <tr><td>{{ $p->isGst ? 'Taxable value' : 'Subtotal' }}</td><td class="r">{{ $n($p->subtotal) }}</td></tr>
        @if ($p->isGst)
            @if ($p->isInterState)
                <tr><td>IGST</td><td class="r">{{ $n($p->taxTotal) }}</td></tr>
            @else
                <tr><td>CGST</td><td class="r">{{ $n($p->cgst) }}</td></tr>
                <tr><td>SGST</td><td class="r">{{ $n($p->sgst) }}</td></tr>
            @endif
        @endif
        @if ($p->discount != 0)<tr><td>Discount</td><td class="r">-{{ $n($p->discount) }}</td></tr>@endif
        @if ($p->transport != 0)<tr><td>Transport</td><td class="r">{{ $n($p->transport) }}</td></tr>@endif
        @if (abs($p->roundOff) >= 0.005)<tr><td>Round off</td><td class="r">{{ $n($p->roundOff) }}</td></tr>@endif
    </table>
    <div class="solid"></div>
    <table class="grand"><tr><td>TOTAL</td><td class="r">{{ $m($p->grandTotal) }}</td></tr></table>
    <div class="solid"></div>

    <table>
        <tr><td>Paid @if ($p->inv('payment_mode'))({{ $p->inv('payment_mode') }})@endif</td><td class="r">{{ $n($p->received) }}</td></tr>
        <tr><td>Previous due</td><td class="r">{{ $n($p->previousDue) }}</td></tr>
        <tr class="b"><td>BALANCE DUE</td><td class="r">{{ $n($p->balanceDue) }}</td></tr>
    </table>

    @if ($p->isGst && count($rateSummary))
        <div class="cut"></div>
        <table class="sm">
            <tr class="b"><td>GST</td><td class="r">Taxable</td><td class="r">Tax</td></tr>
            @foreach ($rateSummary as $rate => $sum)
                <tr><td>{{ $rate }}</td><td class="r">{{ $n($sum['taxable']) }}</td><td class="r">{{ $n($sum['tax']) }}</td></tr>
            @endforeach
        </table>
    @endif

    <div class="cut"></div>
    <div class="sm">{{ $p->amountInWords }}</div>

    @if ($p->biz('upi_qr'))
        <div class="c" style="margin-top: 6px;">
            <img src="{{ $p->biz('upi_qr') }}" alt="UPI payment QR code" style="width: 30mm; height: 30mm;">
            <div>Scan to pay by UPI</div>
        </div>
    @endif

    <div class="cut"></div>
    <div class="c">
        <div>Items: {{ count($p->rows) }} | Qty: {{ \App\Support\InvoicePresenter::qty($totalQty) }}</div>
        @if ($p->biz('terms'))<div class="sm" style="margin-top: 3px;">{!! nl2br(e($p->biz('terms'))) !!}</div>@endif
        <div class="b" style="margin-top: 5px;">Thank you! Visit again.</div>
    </div>
</div>
</body>
</html>
