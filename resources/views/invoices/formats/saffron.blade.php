{{-- Invoice format 5: Saffron. Warm, colourful layout; renders GST and non-GST bills. --}}
@php
    $p = new \App\Support\InvoicePresenter($business, $invoice, $customer, $items);
    $forPdf = $forPdf ?? false;
    $m = fn ($v) => \App\Support\InvoicePresenter::money($v);
    $n = fn ($v) => \App\Support\InvoicePresenter::num($v);
    $accent = $accent ?? '#EA6A0C';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p->title() }} {{ $p->inv('number') }}</title>
@unless ($forPdf)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
@endunless
<style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; background: {{ $forPdf ? '#FFFFFF' : '#EFEAE4' }}; color: #3A2414; font-size: 11px; line-height: 1.5;
        font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'Poppins', 'DejaVu Sans', Arial, sans-serif" !!}; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }
    .sheet { width: 794px; max-width: 100%; min-height: 1123px; margin: {{ $forPdf ? '0' : '20px auto' }}; background: #FFFFFF; }
    .band { background: {{ $accent }}; color: #FFFFFF; padding: 30px 36px 26px; }
    .strip { height: 6px; background: #8E1B1B; }
    .box { background: #FFF4E8; border-radius: 12px; padding: 12px 14px; }
    .lb { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #9A5A26; }
    .items th { background: #FFE2C6; color: #8A3B00; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; padding: 9px 10px; text-align: left; }
    .items td { padding: 9px 10px; border-bottom: 1px solid #F6DDC6; }
    .items tr.alt td { background: #FFFAF3; }
    .items thead { display: table-header-group; }
    .items tr { page-break-inside: avoid; }
    .r { text-align: right !important; }
    .c { text-align: center !important; }
    .sum td { padding: 4px 0; }
    .grand td { vertical-align: middle; }
    .grand { background: {{ $accent }}; color: #FFFFFF; border-radius: 10px; padding: 10px 14px; font-weight: 700; }
    .toolbar { width: 794px; max-width: 100%; margin: 16px auto 0; text-align: right; }
    .toolbar button { height: 40px; padding: 0 18px; border: 0; border-radius: 10px; background: {{ $accent }}; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer; }
    @media print { body { background: #FFFFFF; } .toolbar { display: none; } .sheet { margin: 0; } }
</style>
</head>
<body>
@unless ($forPdf)<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>@endunless
<div class="sheet">
    <div class="band">
        <table><tr>
            <td style="width: 64px;">
                @if ($p->logoSrc)
                    <img src="{{ $p->logoSrc }}" alt="{{ $p->biz('name') }} logo" style="width: 52px; height: 52px; border-radius: 14px; background: #FFFFFF;">
                @else
                    <div style="width: 52px; height: 52px; border-radius: 14px; background: #FFFFFF; color: {{ $accent }}; font-size: 18px; font-weight: 700; text-align: center; line-height: 52px;">{{ $p->initials }}</div>
                @endif
            </td>
            <td>
                <div style="font-size: 20px; font-weight: 700; line-height: 1.2;">{{ $p->biz('name') }}</div>
                <div style="color: #FFE7D1;">{{ $p->biz('address') }}@if ($p->biz('phone')) | {{ $p->biz('phone') }}@endif</div>
                @if ($p->isGst && $p->biz('gstin'))<div style="color: #FFE7D1;">GSTIN {{ $p->biz('gstin') }}</div>@endif
            </td>
            <td class="r" style="width: 210px;">
                <div style="font-size: 24px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">{{ $p->title() }}</div>
                <div style="font-size: 13px; font-weight: 600;">{{ $p->inv('number') }}</div>
            </td>
        </tr></table>
    </div>
    <div class="strip"></div>

    <div style="padding: 24px 36px 0;">
        <table><tr>
            <td style="width: 46%; padding-right: 10px;">
                <div class="box" style="min-height: 92px;">
                    <div class="lb">Bill to</div>
                    <div style="font-size: 14px; font-weight: 700;">{{ $p->cust('name') }}</div>
                    <div>{{ $p->cust('address') }}@if ($p->cust('phone')) | {{ $p->cust('phone') }}@endif</div>
                    @if ($p->isGst && $p->cust('gstin'))<div>GSTIN {{ $p->cust('gstin') }}</div>@endif
                </div>
            </td>
            <td style="width: 27%; padding-right: 10px;">
                <div class="box" style="min-height: 92px;">
                    <div class="lb">Date</div>
                    <div style="font-size: 13px; font-weight: 700;">{{ $p->dateText }}</div>
                    @if ($p->isGst)
                        <div class="lb" style="margin-top: 6px;">Place of supply</div>
                        <div style="font-weight: 600;">{{ $p->inv('place_of_supply_name', $p->biz('state_name')) }} ({{ $p->supplyState }})</div>
                    @else
                        <div class="lb" style="margin-top: 6px;">Payment</div>
                        <div style="font-weight: 600;">{{ $p->inv('payment_mode', '-') }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 27%;">
                <div class="box" style="min-height: 92px; background: #8E1B1B; color: #FFFFFF;">
                    <div class="lb" style="color: #FFD3B0;">Balance due</div>
                    <div style="font-size: 19px; font-weight: 700;">{{ $m($p->balanceDue) }}</div>
                    <div style="color: #FFD3B0;">{{ $p->statusLabel }}</div>
                </div>
            </td>
        </tr></table>

        <table class="items" style="margin-top: 22px;">
            <thead><tr>
                <th style="width: 30px; border-radius: 8px 0 0 8px;">#</th>
                <th>Item</th>
                <th class="r">Qty</th>
                <th class="r">Rate</th>
                @if ($p->isGst)<th class="r">Taxable</th><th class="r">GST</th>@endif
                <th class="r" style="border-radius: 0 8px 8px 0;">Amount</th>
            </tr></thead>
            <tbody>
            @foreach ($p->rows as $i => $row)
                <tr class="{{ $i % 2 ? 'alt' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td><b>{{ $row['name'] }}</b>@if ($p->isGst && $row['hsn'])<div style="font-size: 9px; color: #9A5A26;">HSN {{ $row['hsn'] }}</div>@endif</td>
                    <td class="r">{{ $row['qty'] }} {{ $row['unit'] }}</td>
                    <td class="r">{{ $n($row['rate']) }}</td>
                    @if ($p->isGst)
                        <td class="r">{{ $n($row['taxable']) }}</td>
                        <td class="r">{{ $row['gst_label'] }}<div style="font-size: 9px; color: #9A5A26;">{{ $n($row['tax']) }}</div></td>
                    @endif
                    <td class="r"><b>{{ $n($row['total']) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table style="margin-top: 22px;"><tr>
            <td style="width: 55%; padding-right: 24px;">
                <div class="lb">Amount in words</div>
                <div style="font-weight: 600; margin-bottom: 14px;">{{ $p->amountInWords }}</div>
                <div class="box">
                    <table><tr>
                        @if ($p->biz('upi_qr'))
                            <td style="width: 96px;"><img src="{{ $p->biz('upi_qr') }}" alt="UPI payment QR code" style="width: 84px; height: 84px; border-radius: 6px;"></td>
                        @endif
                        <td>
                            <div class="lb">Scan &amp; pay / Bank</div>
                            <div style="font-weight: 700;">{{ $p->biz('bank_name') }}</div>
                            <div>A/c {{ $p->biz('account_no') }}</div>
                            <div>{{ $p->biz('account_holder') }}</div>
                            <div>IFSC {{ $p->biz('ifsc') }}</div>
                        </td>
                    </tr></table>
                </div>
            </td>
            <td>
                <table class="sum">
                    <tr><td>{{ $p->isGst ? 'Taxable value' : 'Subtotal' }}</td><td class="r">{{ $m($p->subtotal) }}</td></tr>
                    @if ($p->isGst)
                        @if ($p->isInterState)
                            <tr><td>IGST</td><td class="r">{{ $m($p->taxTotal) }}</td></tr>
                        @else
                            <tr><td>CGST</td><td class="r">{{ $m($p->cgst) }}</td></tr>
                            <tr><td>SGST</td><td class="r">{{ $m($p->sgst) }}</td></tr>
                        @endif
                    @endif
                    @if ($p->discount != 0)<tr><td>Discount</td><td class="r">- {{ $m($p->discount) }}</td></tr>@endif
                    @if ($p->transport != 0)<tr><td>Transport</td><td class="r">{{ $m($p->transport) }}</td></tr>@endif
                    @if (abs($p->roundOff) >= 0.005)<tr><td>Round off</td><td class="r">{{ $m($p->roundOff) }}</td></tr>@endif
                </table>
                <div class="grand" style="margin: 8px 0;"><table><tr><td style="font-size: 13px;">Total</td><td class="r" style="font-size: 18px;">{{ $m($p->grandTotal) }}</td></tr></table></div>
                <table class="sum">
                    <tr><td>Received</td><td class="r" style="color: #1E7B3A; font-weight: 700;">{{ $m($p->received) }}</td></tr>
                    <tr><td>Previous due</td><td class="r">{{ $m($p->previousDue) }}</td></tr>
                    <tr><td style="font-weight: 700; border-top: 1px dashed #E9C7A6; padding-top: 6px;">Balance due</td><td class="r" style="font-weight: 700; color: #8E1B1B; border-top: 1px dashed #E9C7A6; padding-top: 6px;">{{ $m($p->balanceDue) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="margin-top: 28px; border-top: 1px solid #F6DDC6;"><tr>
            <td style="padding-top: 12px; color: #7A5A44;">
                @if ($p->biz('terms'))<div class="lb">Terms</div><div>{!! nl2br(e($p->biz('terms'))) !!}</div>@endif
                <div style="margin-top: 10px; font-size: 14px; font-weight: 700; color: {{ $accent }};">Thank you for your business!</div>
            </td>
            <td class="r" style="width: 220px; padding-top: 12px;">
                <div style="height: 54px;"></div>
                <div style="border-top: 1px solid #3A2414; padding-top: 4px; text-align: center;">For {{ $p->biz('name') }}</div>
            </td>
        </tr></table>
    </div>
</div>
</body>
</html>
