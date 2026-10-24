{{-- Invoice format 8: Bill Book. Traditional ruled cash/credit memo look; renders GST and non-GST bills. --}}
@php
    $p = new \App\Support\InvoicePresenter($business, $invoice, $customer, $items);
    $forPdf = $forPdf ?? false;
    $m = fn ($v) => \App\Support\InvoicePresenter::money($v);
    $n = fn ($v) => \App\Support\InvoicePresenter::num($v);
    $accent = $accent ?? '#8B1E1E';
    $ruledRows = max(12, count($p->rows));
    $cols = $p->isGst ? 7 : 5;
    $serif = $forPdf ? "'DejaVu Serif', serif" : "'Noto Serif', 'DejaVu Serif', Georgia, serif";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $p->title() }} {{ $p->inv('number') }}</title>
@unless ($forPdf)
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@600;700&family=Noto+Sans:wght@400;600;700&display=swap" rel="stylesheet">
@endunless
<style>
    @page { size: A4; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; background: {{ $forPdf ? '#FFFFFF' : '#E8E2D6' }}; color: #2B1A12; font-size: 11px; line-height: 1.45;
        font-family: {!! $forPdf ? "'DejaVu Sans', sans-serif" : "'Noto Sans', 'DejaVu Sans', Arial, sans-serif" !!}; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }
    .sheet { width: 794px; max-width: 100%; min-height: 1123px; margin: {{ $forPdf ? '0' : '20px auto' }}; background: #FFFDF4; padding: 34px; }
    .frame { border: 2px solid {{ $accent }}; padding: 4px; }
    .inner { border: 1px solid {{ $accent }}; }
    .mr { color: {{ $accent }}; }
    .fill { border-bottom: 1px dotted {{ $accent }}; display: inline-block; min-width: 120px; padding: 0 4px; font-weight: 700; }
    .ruled th { color: {{ $accent }}; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 7px 6px; border-bottom: 1.5px solid {{ $accent }}; border-right: 1px solid {{ $accent }}; }
    .ruled td { padding: 6px; height: 26px; border-bottom: 1px solid #E9C9C0; border-right: 1px solid {{ $accent }}; }
    .ruled th:last-child, .ruled td:last-child { border-right: 0; }
    .ruled thead { display: table-header-group; }
    .ruled tr { page-break-inside: avoid; }
    .r { text-align: right !important; }
    .c { text-align: center !important; }
    .tl td { padding: 5px 8px; border-top: 1px solid {{ $accent }}; }
    .toolbar { width: 794px; max-width: 100%; margin: 16px auto 0; text-align: right; }
    .toolbar button { height: 40px; padding: 0 18px; border: 0; border-radius: 10px; background: {{ $accent }}; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer; }
    @media print { body { background: #FFFFFF; } .toolbar { display: none; } .sheet { margin: 0; } }
</style>
</head>
<body>
@unless ($forPdf)<div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>@endunless
<div class="sheet">
<div class="frame"><div class="inner">

    <table style="border-bottom: 1.5px solid {{ $accent }};"><tr>
        <td style="width: 30%; padding: 10px 12px;" class="mr">
            @if ($p->isGst && $p->biz('gstin'))<div style="font-size: 10px;"><b>GSTIN:</b> {{ $p->biz('gstin') }}</div>@endif
            @if ($p->biz('phone'))<div style="font-size: 10px;"><b>Mo.</b> {{ $p->biz('phone') }}</div>@endif
        </td>
        <td class="c" style="padding: 10px 0;">
            <span style="display: inline-block; border: 1.5px solid {{ $accent }}; border-radius: 20px; padding: 2px 16px; font-size: 11px; font-weight: 700; letter-spacing: 2px;" class="mr">{{ $p->isGst ? 'TAX INVOICE' : 'CASH / CREDIT MEMO' }}</span>
        </td>
        <td class="r mr" style="width: 30%; padding: 10px 12px; font-size: 10px;">Original</td>
    </tr></table>

    <div class="c" style="padding: 12px 16px 14px; border-bottom: 1.5px solid {{ $accent }};">
        <div class="mr" style="font-family: {!! $serif !!}; font-size: 28px; font-weight: 700; line-height: 1.2;">{{ $p->biz('name') }}</div>
        <div class="mr" style="font-size: 12px;">{{ $p->biz('address') }}</div>
    </div>

    <table style="border-bottom: 1.5px solid {{ $accent }};"><tr>
        <td style="padding: 10px 12px;"><span class="mr">No.</span> <span class="fill" style="color: {{ $accent }}; font-size: 14px;">{{ $p->inv('number') }}</span></td>
        <td class="r" style="padding: 10px 12px;"><span class="mr">Date</span> <span class="fill">{{ $p->dateText }}</span></td>
    </tr><tr>
        <td colspan="2" style="padding: 2px 12px 10px;">
            <span class="mr">M/s.</span> <span class="fill" style="min-width: 300px;">{{ $p->cust('name') }}@if ($p->cust('address')), {{ $p->cust('address') }}@endif</span>
            <span class="mr" style="margin-left: 12px;">Mo.</span> <span class="fill">{{ $p->cust('phone') }}</span>
            @if ($p->isGst && $p->cust('gstin'))<div style="margin-top: 6px;"><span class="mr">GSTIN</span> <span class="fill">{{ $p->cust('gstin') }}</span> <span class="mr" style="margin-left: 12px;">Place of supply</span> <span class="fill">{{ $p->inv('place_of_supply_name', $p->biz('state_name')) }} ({{ $p->supplyState }})</span></div>@endif
        </td>
    </tr></table>

    <table class="ruled">
        <thead><tr>
            <th style="width: 40px;">Sr.</th>
            <th style="text-align: left;">Particulars</th>
            @if ($p->isGst)<th style="width: 60px;">HSN</th>@endif
            <th style="width: 80px;" class="r">Qty</th>
            <th style="width: 86px;" class="r">Rate</th>
            @if ($p->isGst)<th style="width: 64px;" class="r">GST</th>@endif
            <th style="width: 110px;" class="r">Amount</th>
        </tr></thead>
        <tbody>
        @for ($i = 0; $i < $ruledRows; $i++)
            @php($row = $p->rows[$i] ?? null)
            <tr>
                <td class="c">{{ $row ? $i + 1 : '' }}</td>
                <td>{{ $row['name'] ?? '' }}</td>
                @if ($p->isGst)<td class="c">{{ $row['hsn'] ?? '' }}</td>@endif
                <td class="r">{{ $row ? $row['qty'] . ' ' . $row['unit'] : '' }}</td>
                <td class="r">{{ $row ? $n($row['rate']) : '' }}</td>
                @if ($p->isGst)<td class="r">{{ $row ? $row['gst_label'] : '' }}@if ($row)<div style="font-size: 9px;" class="mr">{{ $n($row['tax']) }}</div>@endif</td>@endif
                <td class="r" style="font-weight: 700;">{{ $row ? $n($row['total']) : '' }}</td>
            </tr>
        @endfor
        </tbody>
    </table>

    <table style="border-top: 1.5px solid {{ $accent }};"><tr>
        <td style="width: 56%; padding: 10px 12px; border-right: 1px solid {{ $accent }};">
            <div class="mr" style="font-size: 10px; font-weight: 700;">RUPEES IN WORDS</div>
            <div class="fill" style="display: block; min-width: 0;">{{ $p->amountInWords }}</div>
            <table style="margin-top: 10px;"><tr>
                @if ($p->biz('upi_qr'))
                    <td style="width: 92px;"><img src="{{ $p->biz('upi_qr') }}" alt="UPI payment QR code" style="width: 82px; height: 82px;"></td>
                @endif
                <td style="font-size: 10px;">
                    <div class="mr" style="font-weight: 700;">BANK DETAILS</div>
                    <div>{{ $p->biz('bank_name') }} | A/c {{ $p->biz('account_no') }}</div>
                    <div>IFSC {{ $p->biz('ifsc') }} | {{ $p->biz('account_holder') }}</div>
                    @if ($p->biz('terms'))<div class="mr" style="margin-top: 6px;">{!! nl2br(e($p->biz('terms'))) !!}</div>@endif
                    <div class="mr" style="margin-top: 4px;">E. &amp; O.E.</div>
                </td>
            </tr></table>
        </td>
        <td style="padding: 0;">
            <table class="tl">
                <tr><td style="border-top: 0;">{{ $p->isGst ? 'Taxable value' : 'Total' }}</td><td class="r" style="border-top: 0;">{{ $m($p->subtotal) }}</td></tr>
                @if ($p->isGst)
                    @if ($p->isInterState)
                        <tr><td>IGST</td><td class="r">{{ $m($p->taxTotal) }}</td></tr>
                    @else
                        <tr><td>CGST</td><td class="r">{{ $m($p->cgst) }}</td></tr>
                        <tr><td>SGST</td><td class="r">{{ $m($p->sgst) }}</td></tr>
                    @endif
                @endif
                @if ($p->discount != 0)<tr><td>Less: Discount</td><td class="r">- {{ $m($p->discount) }}</td></tr>@endif
                @if ($p->transport != 0)<tr><td>Add: Transport</td><td class="r">{{ $m($p->transport) }}</td></tr>@endif
                @if (abs($p->roundOff) >= 0.005)<tr><td>Round off</td><td class="r">{{ $m($p->roundOff) }}</td></tr>@endif
                <tr style="background: {{ $accent }}; color: #FFFFFF;"><td style="font-weight: 700; font-size: 13px;">Grand Total</td><td class="r" style="font-weight: 700; font-size: 14px;">{{ $m($p->grandTotal) }}</td></tr>
                <tr><td>Received</td><td class="r">{{ $m($p->received) }}</td></tr>
                <tr><td>Old balance</td><td class="r">{{ $m($p->previousDue) }}</td></tr>
                <tr><td style="font-weight: 700;" class="mr">Net balance</td><td class="r mr" style="font-weight: 700;">{{ $m($p->balanceDue) }}</td></tr>
            </table>
        </td>
    </tr></table>

    <table style="border-top: 1.5px solid {{ $accent }};"><tr>
        <td style="padding: 10px 12px;" class="mr">Receiver's signature<div style="height: 44px;"></div></td>
        <td class="r mr" style="padding: 10px 12px;"><b>For, {{ $p->biz('name') }}</b><div style="height: 44px;"></div>Authorised signatory</td>
    </tr></table>

</div></div>
</div>
</body>
</html>
