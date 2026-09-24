<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 20px; }
        .invoice-box { border: 1.5px solid #1f2937; padding: 24px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .header h1 { font-size: 20px; margin: 0 0 4px 0; }
        .header .branch { color: #4b5563; }
        .header .invoice-meta { text-align: right; }
        .invoice-title { font-size: 22px; font-weight: bold; color: #4f46e5; margin: 0 0 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .info-table td { vertical-align: top; padding-bottom: 16px; }
        .info-label { color: #6b7280; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .items-table { border: 1px solid #9ca3af; }
        .items-table th { background: #f3f4f6; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; color: #6b7280; border: 1px solid #9ca3af; }
        .items-table td { padding: 8px; border: 1px solid #d1d5db; }
        .items-table .num { text-align: right; }
        .totals { width: 260px; float: right; margin-top: 16px; }
        .totals td { padding: 4px 8px; }
        .totals .label { color: #4b5563; }
        .totals .value { text-align: right; }
        .totals .grand { font-size: 14px; font-weight: bold; border-top: 1px solid #d1d5db; }
        .balance-due { color: #dc2626; }
        .balance-paid { color: #16a34a; }
        .payment-section { margin-top: 16px; }
        .payment-qr { float: left; width: 150px; }
        .payment-qr img { width: 110px; height: 110px; }
        .payment-qr .qr-label { color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: .03em; margin-bottom: 6px; }
        .payment-qr .bank-details { margin-top: 6px; font-size: 10px; color: #4b5563; line-height: 1.5; }
        .payment-qr .bank-details .bank-name { font-weight: bold; color: #1f2937; }
        .clearfix { clear: both; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
    <div class="invoice-box">
    <div class="header">
        <div>
            <h1>{{ $invoice->branch->name }}</h1>
            @if ($invoice->branch->address)
                <div class="branch">{{ $invoice->branch->address }}</div>
            @endif
            @if ($invoice->branch->phone)
                <div class="branch">{{ $invoice->branch->phone }}</div>
            @endif
        </div>
        <div class="invoice-meta">
            <div class="invoice-title">INVOICE</div>
            <div><strong>{{ $invoice->invoice_number }}</strong></div>
            <div>{{ $invoice->invoice_date->format('d M Y') }}</div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-label">Billed To</div>
                <div><strong>{{ $invoice->customer->name }}</strong></div>
                @if ($invoice->customer->phone)
                    <div>{{ $invoice->customer->phone }}</div>
                @endif
                @if ($invoice->customer->address)
                    <div>{{ $invoice->customer->address }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Rate</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $line)
                <tr>
                    <td>{{ $line->item->name }}</td>
                    <td class="num">{{ rtrim(rtrim((string) $line->quantity, '0'), '.') }} {{ $line->item->unit }}</td>
                    <td class="num">₹{{ number_format($line->unit_price, 2) }}</td>
                    <td class="num">₹{{ number_format($line->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="payment-section">
        <div class="payment-qr">
            <div class="qr-label">Scan &amp; Pay</div>
            <img src="{{ 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/payment-qr.png'))) }}" alt="Payment QR">
            <div class="bank-details">
                <div class="bank-name">HDFC Bank</div>
                <div>A/c No: 50200097420397</div>
                <div>A/c Holder: Om Sai Aalubhandar</div>
                <div>IFSC: HDFC0004196</div>
            </div>
        </div>

        <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">₹{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if ($invoice->discount > 0)
            <tr>
                <td class="label">Discount</td>
                <td class="value">-₹{{ number_format($invoice->discount, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->tax > 0)
            <tr>
                <td class="label">Tax</td>
                <td class="value">₹{{ number_format($invoice->tax, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->transportation > 0)
            <tr>
                <td class="label">Transportation</td>
                <td class="value">₹{{ number_format($invoice->transportation, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="label">Total</td>
            <td class="value">₹{{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Paid</td>
            <td class="value">₹{{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        @php
            $previousDue = $customerDue - ($invoice->total - $invoice->paid_amount);
        @endphp
        <tr>
            <td class="label" style="border-top:1px solid #d1d5db;padding-top:8px;">Previous Due</td>
            <td class="value {{ $previousDue > 0 ? 'balance-due' : ($previousDue < 0 ? 'balance-paid' : '') }}" style="border-top:1px solid #d1d5db;padding-top:8px;">
                ₹{{ number_format(abs($previousDue), 2) }}{{ $previousDue < 0 ? ' CR' : '' }}
            </td>
        </tr>
        <tr class="grand">
            <td class="label">{{ $customerDue > 0 ? 'Balance Due' : 'Settled / In Credit' }}</td>
            <td class="value {{ $customerDue > 0 ? 'balance-due' : 'balance-paid' }}">
                ₹{{ number_format(abs($customerDue), 2) }}{{ $customerDue < 0 ? ' CR' : '' }}
            </td>
        </tr>
        </table>
        <div class="clearfix"></div>
    </div>

    @if ($invoice->notes)
        <div style="margin-top: 20px;">
            <div class="info-label">Notes</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="footer">Thank you for your business!</div>
    </div>
</body>
</html>
