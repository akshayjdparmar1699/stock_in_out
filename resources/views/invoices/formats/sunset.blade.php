{{-- Invoice format 7: Sunset. Bright coral and peach layout with a paid/due stamp; renders GST and non-GST bills. --}}
@php
    $p = new \App\Support\InvoicePresenter($business, $invoice, $customer, $items);
    $forPdf = $forPdf ?? false;
    $m = fn ($v) => \App\Support\InvoicePresenter::money($v);
    $n = fn ($v) => \App\Support\InvoicePresenter::num($v);
    $accent = $accent ?? '#F2545B';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p->title() }} {{ $p->inv('number') }}</title>
@unless ($forPdf)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endunless
<style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; background: {{ $forPdf ? '#FFFFFF' : '#F3E9E6' }}; color: #3B1F2B; font-size: 11px; line-height: 1.5;
        font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'Outfit', 'DejaVu Sans', Arial, sans-serif" !!}; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }
    .sheet { position: relative; width: 794px; max-width: 100%; min-height: 1123px; margin: {{ $forPdf ? '0' : '20px auto' }}; background: #FFFFFF; overflow: hidden; }
    .top { background: {{ $accent }}; color: #FFFFFF; padding: 34px 40px 70px; text-align: center; }
    .top2 { height: 10px; background: #FFB347; }
    .card { background: #FFFFFF; border-radius: 18px; border: 1px solid #F6D5CF; }
    .lb { font-size: 9px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #C2414A; }
    .items th { background: #FFE6DE; color: #9E2A35; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 9px 10px; text-align: left; }
    .items td { padding: 10px; border-bottom: 1px dashed #F3CFC7; }
    .items thead { display: table-header-group; }
    .items tr { page-break-inside: avoid; }
    .r { text-align: right !important; }
    .sum td { padding: 4px 0; }
    .stamp { position: absolute; right: 48px; top: 40px; color: #FFFFFF; border-color: #FFFFFF; border: 3px solid; border-radius: 10px; padding: 4px 14px; font-size: 22px; font-weight: 800; letter-spacing: 3px; transform: rotate(-12deg); opacity: 0.85; }
    .toolbar { width: 794px; max-width: 100%; margin: 16px auto 0; text-align: right; }
    .toolbar button { height: 40px; padding: 0 18px; border: 0; border-radius: 10px; background: {{ $accent }}; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer; }
    @media print { body { background: #FFFFFF; } .toolbar { display: none; } .sheet { margin: 0; } }
</style>
</head>
<body>
@unless ($forPdf)<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>@endunless
<div class="sheet">
    <div class="top">
        <div class="stamp">{{ $p->isPaid() ? 'PAID' : 'DUE' }}</div>
        @if ($p->logoSrc)
            <img src="{{ $p->logoSrc }}" alt="{{ $p->biz('name') }} logo" style="width: 56px; height: 56px; border-radius: 50%; background: #FFFFFF;">
        @else
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #FFFFFF; color: {{ $accent }}; font-size: 19px; font-weight: 800; line-height: 56px; margin: 0 auto;">{{ $p->initials }}</div>
        @endif
        <div style="font-size: 22px; font-weight: 800; margin-top: 8px;">{{ $p->biz('name') }}</div>
        <div style="color: #FFE1DC;">{{ $p->biz('address') }}@if ($p->biz('phone')) | {{ $p->biz('phone') }}@endif @if ($p->isGst && $p->biz('gstin')) | GSTIN {{ $p->biz('gstin') }}@endif</div>
    </div>
    <div class="top2"></div>

    <div style="padding: 0 40px;">
        <div class="card" style="margin-top: -56px; position: relative; padding: 18px 20px;">
            <table><tr>
                <td style="width: 38%;">
                    <div class="lb">Billed to</div>
                    <div style="font-size: 15px; font-weight: 700;">{{ $p->cust('name') }}</div>
                    <div>{{ $p->cust('phone') }}@if ($p->cust('address')) | {{ $p->cust('address') }}@endif</div>
                    @if ($p->isGst && $p->cust('gstin'))<div>GSTIN {{ $p->cust('gstin') }}</div>@endif
                </td>
                <td style="width: 30%;">
                    <div class="lb">{{ $p->title() }}</div>
                    <div style="font-size: 14px; font-weight: 700;">{{ $p->inv('number') }}</div>
                    <div>{{ $p->dateText }}</div>
                </td>
                <td class="r">
                    <div class="lb">Total amount</div>
                    <div style="font-size: 26px; font-weight: 800; color: {{ $accent }}; line-height: 1.1;">{{ $m($p->grandTotal) }}</div>
                    @if ($p->isGst)<div style="color: #8A5A62;">incl. GST {{ $m($p->taxTotal) }}</div>@endif
                </td>
            </tr></table>
        </div>

        
        <table class="items" style="margin-top: 26px;">
            <thead><tr>
                <th style="border-radius: 10px 0 0 10px;">Item</th>
                <th class="r">Qty</th>
                <th class="r">Rate</th>
                @if ($p->isGst)<th class="r">Taxable</th><th class="r">GST</th>@endif
                <th class="r" style="border-radius: 0 10px 10px 0;">Amount</th>
            </tr></thead>
            <tbody>
            @foreach ($p->rows as $row)
                <tr>
                    <td><b>{{ $row['name'] }}</b>@if ($p->isGst && $row['hsn'])<span style="color: #B07A82; font-size: 9px;"> | HSN {{ $row['hsn'] }}</span>@endif</td>
                    <td class="r">{{ $row['qty'] }} {{ $row['unit'] }}</td>
                    <td class="r">{{ $n($row['rate']) }}</td>
                    @if ($p->isGst)
                        <td class="r">{{ $n($row['taxable']) }}</td>
                        <td class="r">{{ $row['gst_label'] }}<div style="font-size: 9px; color: #B07A82;">{{ $n($row['tax']) }}</div></td>
                    @endif
                    <td class="r"><b>{{ $n($row['total']) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table style="margin-top: 22px;"><tr>
            <td style="width: 50%; padding-right: 24px;">
                <div style="background: #FFF4EF; border-radius: 16px; padding: 14px 16px;">
                    <table><tr>
                        @if ($p->biz('upi_qr'))
                            <td style="width: 100px;"><img src="{{ $p->biz('upi_qr') }}" alt="UPI payment QR code" style="width: 88px; height: 88px; border-radius: 8px;"></td>
                        @endif
                        <td>
                            <div class="lb">Scan to pay</div>
                            <div style="font-weight: 700;">{{ $p->biz('bank_name') }}</div>
                            <div>A/c {{ $p->biz('account_no') }}</div>
                            <div>IFSC {{ $p->biz('ifsc') }}</div>
                        </td>
                    </tr></table>
                </div>
                <div class="lb" style="margin-top: 14px;">Amount in words</div>
                <div style="font-weight: 600;">{{ $p->amountInWords }}</div>
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
                    <tr><td style="font-size: 15px; font-weight: 800; padding-top: 8px; border-top: 2px solid #3B1F2B;">Total</td><td class="r" style="font-size: 15px; font-weight: 800; padding-top: 8px; border-top: 2px solid #3B1F2B;">{{ $m($p->grandTotal) }}</td></tr>
                    <tr><td>Received</td><td class="r">{{ $m($p->received) }}</td></tr>
                    <tr><td>Previous due</td><td class="r">{{ $m($p->previousDue) }}</td></tr>
                </table>
                <table style="margin-top: 8px; background: #3B1F2B; color: #FFFFFF; border-radius: 12px;"><tr>
                    <td style="padding: 11px 14px; font-weight: 700; vertical-align: middle;">Balance due</td>
                    <td class="r" style="padding: 11px 14px; font-size: 17px; font-weight: 800; color: #FFB347;">{{ $m($p->balanceDue) }}</td>
                </tr></table>
            </td>
        </tr></table>

        <div style="margin-top: 30px; border-top: 2px dashed #F3CFC7; padding-top: 14px;">
            <table><tr>
                <td style="color: #8A5A62;">@if ($p->biz('terms')){!! nl2br(e($p->biz('terms'))) !!}@endif</td>
                <td class="r" style="width: 210px;"><div style="height: 40px;"></div><div style="border-top: 1px solid #3B1F2B; padding-top: 4px; text-align: center;">For {{ $p->biz('name') }}</div></td>
            </tr></table>
            <div style="text-align: center; margin-top: 18px; font-size: 15px; font-weight: 800; color: {{ $accent }};">Thank you, visit again!</div>
        </div>
    </div>
</div>
</body>
</html>
