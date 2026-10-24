{{-- Invoice format 6: Teal Minimal. Clean modern layout; renders GST and non-GST bills. --}}
@php
    $p = new \App\Support\InvoicePresenter($business, $invoice, $customer, $items);
    $forPdf = $forPdf ?? false;
    $m = fn ($v) => \App\Support\InvoicePresenter::money($v);
    $n = fn ($v) => \App\Support\InvoicePresenter::num($v);
    $accent = $accent ?? '#0F766E';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p->title() }} {{ $p->inv('number') }}</title>
@unless ($forPdf)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
@endunless
<style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; background: {{ $forPdf ? '#FFFFFF' : '#E9EFEE' }}; color: #12302D; font-size: 11px; line-height: 1.5;
        font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'DM Sans', 'DejaVu Sans', Arial, sans-serif" !!}; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }
    .sheet { width: 794px; max-width: 100%; min-height: 1123px; margin: {{ $forPdf ? '0' : '20px auto' }}; background: #FFFFFF; border-left: 14px solid {{ $accent }}; padding: 40px 44px; }
    .lb { font-size: 9px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: {{ $accent }}; }
    .items th { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: {{ $accent }}; padding: 10px 8px; border-bottom: 2px solid {{ $accent }}; text-align: left; }
    .items td { padding: 11px 8px; border-bottom: 1px solid #E3ECEA; }
    .items thead { display: table-header-group; }
    .items tr { page-break-inside: avoid; }
    .r { text-align: right !important; }
    .sum td { padding: 4px 0; }
    .pill { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; }
    .toolbar { width: 794px; max-width: 100%; margin: 16px auto 0; text-align: right; }
    .toolbar button { height: 40px; padding: 0 18px; border: 0; border-radius: 10px; background: {{ $accent }}; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer; }
    @media print { body { background: #FFFFFF; } .toolbar { display: none; } .sheet { margin: 0; } }
</style>
</head>
<body>
@unless ($forPdf)<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>@endunless
<div class="sheet">
    <table><tr>
        <td>
            @if ($p->logoSrc)
                <img src="{{ $p->logoSrc }}" alt="{{ $p->biz('name') }} logo" style="width: 44px; height: 44px; border-radius: 10px;">
            @else
                <div style="width: 44px; height: 44px; border-radius: 10px; background: {{ $accent }}; color: #FFFFFF; font-size: 16px; font-weight: 700; text-align: center; line-height: 44px;">{{ $p->initials }}</div>
            @endif
            <div style="font-size: 17px; font-weight: 700; margin-top: 10px;">{{ $p->biz('name') }}</div>
            <div style="color: #4F6B67;">{{ $p->biz('address') }}@if ($p->biz('phone')) | {{ $p->biz('phone') }}@endif</div>
            @if ($p->isGst && $p->biz('gstin'))<div style="color: #4F6B67;">GSTIN {{ $p->biz('gstin') }}</div>@endif
        </td>
        <td class="r">
            <div style="font-size: 40px; font-weight: 700; letter-spacing: 4px; color: #CFE6E2; line-height: 1; text-transform: uppercase;">{{ $p->isGst ? 'Tax Invoice' : 'Invoice' }}</div>
            <div style="font-size: 15px; font-weight: 700; margin-top: 8px;">{{ $p->inv('number') }}</div>
            <div style="color: #4F6B67;">{{ $p->dateText }}</div>
            <div style="margin-top: 6px;"><span class="pill" style="background: {{ $p->isPaid() ? '#DDF5EA' : '#FDE7E4' }}; color: {{ $p->isPaid() ? '#11734A' : '#B42318' }};">{{ $p->statusLabel }}</span></div>
        </td>
    </tr></table>

    <table style="margin-top: 30px; border-top: 1px solid #E3ECEA;"><tr>
        <td style="width: 50%; padding-top: 16px;">
            <div class="lb">Billed to</div>
            <div style="font-size: 15px; font-weight: 700; margin-top: 2px;">{{ $p->cust('name') }}</div>
            <div style="color: #4F6B67;">{{ $p->cust('address') }}</div>
            <div style="color: #4F6B67;">{{ $p->cust('phone') }}</div>
            @if ($p->isGst && $p->cust('gstin'))<div style="color: #4F6B67;">GSTIN {{ $p->cust('gstin') }}</div>@endif
        </td>
        <td style="padding-top: 16px;">
            <table>
                @if ($p->isGst)
                    <tr><td class="lb" style="padding: 2px 0;">Place of supply</td><td class="r" style="font-weight: 700;">{{ $p->inv('place_of_supply_name', $p->biz('state_name')) }} ({{ $p->supplyState }})</td></tr>
                    <tr><td class="lb" style="padding: 2px 0;">Tax type</td><td class="r" style="font-weight: 700;">{{ $p->isInterState ? 'IGST' : 'CGST + SGST' }}</td></tr>
                @endif
                <tr><td class="lb" style="padding: 2px 0;">Payment mode</td><td class="r" style="font-weight: 700;">{{ $p->inv('payment_mode', '-') }}</td></tr>
                <tr><td class="lb" style="padding: 2px 0;">Amount due</td><td class="r" style="font-weight: 700; font-size: 15px; color: {{ $p->isPaid() ? '#11734A' : '#B42318' }};">{{ $m($p->balanceDue) }}</td></tr>
            </table>
        </td>
    </tr></table>

    <table class="items" style="margin-top: 28px;">
        <thead><tr>
            <th style="width: 28px;">#</th>
            <th>Description</th>
            @if ($p->isGst)<th>HSN</th>@endif
            <th class="r">Qty</th>
            <th class="r">Rate</th>
            @if ($p->isGst)<th class="r">GST</th>@endif
            <th class="r">Amount</th>
        </tr></thead>
        <tbody>
        @foreach ($p->rows as $i => $row)
            <tr>
                <td style="color: #8AA5A1;">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td style="font-weight: 700;">{{ $row['name'] }}</td>
                @if ($p->isGst)<td style="color: #4F6B67;">{{ $row['hsn'] ?: '-' }}</td>@endif
                <td class="r">{{ $row['qty'] }} {{ $row['unit'] }}</td>
                <td class="r">{{ $n($row['rate']) }}</td>
                @if ($p->isGst)<td class="r">{{ $row['gst_label'] }}<div style="font-size: 9px; color: #8AA5A1;">{{ $n($row['tax']) }}</div></td>@endif
                <td class="r" style="font-weight: 700;">{{ $n($row['total']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table style="margin-top: 24px;"><tr>
        <td style="width: 52%; padding-right: 28px;">
            <div class="lb">Amount in words</div>
            <div style="font-weight: 700;">{{ $p->amountInWords }}</div>
            @if ($p->inv('note'))<div class="lb" style="margin-top: 12px;">Note</div><div>{{ $p->inv('note') }}</div>@endif
        </td>
        <td>
            <table class="sum">
                <tr><td style="color: #4F6B67;">{{ $p->isGst ? 'Taxable value' : 'Subtotal' }}</td><td class="r">{{ $m($p->subtotal) }}</td></tr>
                @if ($p->isGst)
                    @if ($p->isInterState)
                        <tr><td style="color: #4F6B67;">IGST</td><td class="r">{{ $m($p->taxTotal) }}</td></tr>
                    @else
                        <tr><td style="color: #4F6B67;">CGST</td><td class="r">{{ $m($p->cgst) }}</td></tr>
                        <tr><td style="color: #4F6B67;">SGST</td><td class="r">{{ $m($p->sgst) }}</td></tr>
                    @endif
                @endif
                @if ($p->discount != 0)<tr><td style="color: #4F6B67;">Discount</td><td class="r">- {{ $m($p->discount) }}</td></tr>@endif
                @if ($p->transport != 0)<tr><td style="color: #4F6B67;">Transport</td><td class="r">{{ $m($p->transport) }}</td></tr>@endif
                @if (abs($p->roundOff) >= 0.005)<tr><td style="color: #4F6B67;">Round off</td><td class="r">{{ $m($p->roundOff) }}</td></tr>@endif
            </table>
            <table style="margin: 10px 0; background: {{ $accent }}; color: #FFFFFF; border-radius: 12px;"><tr>
                <td style="padding: 12px 14px; font-weight: 700; vertical-align: middle;">Total</td>
                <td class="r" style="padding: 12px 14px; font-size: 20px; font-weight: 700;">{{ $m($p->grandTotal) }}</td>
            </tr></table>
            <table class="sum">
                <tr><td style="color: #4F6B67;">Received</td><td class="r">{{ $m($p->received) }}</td></tr>
                <tr><td style="color: #4F6B67;">Previous due</td><td class="r">{{ $m($p->previousDue) }}</td></tr>
                <tr><td style="font-weight: 700;">Balance due</td><td class="r" style="font-weight: 700;">{{ $m($p->balanceDue) }}</td></tr>
            </table>
        </td>
    </tr></table>

    <table style="margin-top: 30px; background: #E8F4F2; border-radius: 14px;"><tr>
        @if ($p->biz('upi_qr'))
            <td style="width: 112px; padding: 14px;"><img src="{{ $p->biz('upi_qr') }}" alt="UPI payment QR code" style="width: 86px; height: 86px; background: #FFFFFF; padding: 4px; border-radius: 8px;"></td>
        @endif
        <td style="padding: 14px;">
            <div class="lb">Payment info</div>
            <div style="font-weight: 700;">{{ $p->biz('bank_name') }}</div>
            <div>A/c {{ $p->biz('account_no') }} | IFSC {{ $p->biz('ifsc') }}</div>
            <div>{{ $p->biz('account_holder') }}</div>
            @if ($p->biz('terms'))<div style="margin-top: 6px; color: #4F6B67;">{!! nl2br(e($p->biz('terms'))) !!}</div>@endif
        </td>
        <td class="r" style="width: 200px; padding: 14px;">
            <div style="height: 50px;"></div>
            <div style="border-top: 1px solid #12302D; padding-top: 4px; text-align: center;">Authorised signatory</div>
        </td>
    </tr></table>
</div>
</body>
</html>
