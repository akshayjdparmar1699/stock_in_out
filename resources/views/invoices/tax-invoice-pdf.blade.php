<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 18px; }
        .invoice-box { border: 1.5px solid #1f2937; padding: 18px; }
        .top-row { width: 100%; }
        .top-row td { vertical-align: top; }
        .tax-invoice-title { font-size: 18px; font-weight: bold; text-align: center; letter-spacing: .04em; margin: 0 0 4px 0; }
        .original-tag { text-align: center; font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 10px; }
        .divider { border-top: 1.5px solid #1f2937; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 6px 0; }
        .info-table .meta-box { text-align: right; white-space: nowrap; }
        .info-label { color: #6b7280; font-size: 9px; text-transform: uppercase; margin-bottom: 2px; }
        .seller-name { font-size: 15px; font-weight: bold; margin: 0 0 2px 0; }
        .party-box { border: 1px solid #9ca3af; padding: 8px 10px; width: 48%; }
        .party-row { width: 100%; }
        .party-row td { vertical-align: top; }
        .items-table { border: 1px solid #9ca3af; margin-top: 12px; font-size: 10px; }
        .items-table th { background: #f3f4f6; text-align: center; padding: 5px 4px; font-size: 9px; text-transform: uppercase; color: #6b7280; border: 1px solid #9ca3af; }
        .items-table td { padding: 5px 4px; border: 1px solid #d1d5db; text-align: center; }
        .items-table td.item-name { text-align: left; }
        .items-table tfoot td { font-weight: bold; background: #f9fafb; }
        .hsn-table { border: 1px solid #9ca3af; margin-top: 14px; font-size: 10px; }
        .hsn-table th { background: #f3f4f6; text-align: center; padding: 5px 4px; font-size: 9px; text-transform: uppercase; color: #6b7280; border: 1px solid #9ca3af; }
        .hsn-table td { padding: 5px 4px; border: 1px solid #d1d5db; text-align: center; }
        .hsn-table tfoot td { font-weight: bold; background: #f9fafb; }
        .words-box { margin-top: 10px; border: 1px solid #9ca3af; padding: 8px 10px; font-size: 10.5px; }
        .totals { width: 260px; float: right; margin-top: 12px; }
        .totals td { padding: 4px 8px; }
        .totals .label { color: #4b5563; }
        .totals .value { text-align: right; }
        .totals .grand { font-size: 13px; font-weight: bold; border-top: 1px solid #d1d5db; }
        .balance-due { color: #dc2626; }
        .balance-paid { color: #16a34a; }
        .payment-section { margin-top: 14px; }
        .payment-qr { float: left; width: 200px; }
        .payment-qr img { width: 100px; height: 100px; }
        .payment-qr .qr-label { color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: .03em; margin-bottom: 6px; }
        .payment-qr .bank-details { margin-top: 6px; font-size: 10px; color: #4b5563; line-height: 1.5; white-space: nowrap; }
        .payment-qr .bank-details .bank-name { font-weight: bold; color: #1f2937; }
        .clearfix { clear: both; }
        .terms { margin-top: 18px; font-size: 9.5px; color: #6b7280; }
        .signatory { margin-top: 36px; text-align: right; font-size: 10.5px; }
        .signatory .for-branch { font-weight: bold; margin-bottom: 36px; }
        .footer { margin-top: 24px; text-align: center; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $isSameState = $invoice->items->contains(fn ($line) => $line->cgst_amount !== null);
        $branch = $invoice->branch;
        $customer = $invoice->customer;
        $branchStateCode = \App\Support\GstStateCodes::code($branch->state) ?: $branch->state_code;
        $customerStateCode = $customer ? (\App\Support\GstStateCodes::code($customer->state) ?: $customer->state_code) : null;

        $subtotal = (float) $invoice->subtotal;
        $totalCgst = (float) $invoice->items->sum('cgst_amount');
        $totalSgst = (float) $invoice->items->sum('sgst_amount');
        $totalIgst = (float) $invoice->items->sum('igst_amount');
        $totalGst = $totalCgst + $totalSgst + $totalIgst;

        $preRoundTotal = round($subtotal - (float) $invoice->discount + $totalGst + (float) $invoice->transportation, 2);
        $roundedTotal = round($preRoundTotal);
        $roundOff = round($roundedTotal - $preRoundTotal, 2);

        $hsnSummary = $invoice->items
            ->groupBy(fn ($line) => $line->hsn_code ?: '')
            ->map(function ($lines, $hsn) {
                return [
                    'hsn_code' => $hsn,
                    'taxable' => (float) $lines->sum('total'),
                    'cgst' => (float) $lines->sum('cgst_amount'),
                    'sgst' => (float) $lines->sum('sgst_amount'),
                    'igst' => (float) $lines->sum('igst_amount'),
                ];
            })
            ->values();
    @endphp

    <div class="invoice-box">
        <div class="tax-invoice-title">TAX INVOICE</div>
        <div class="original-tag">Original for Recipient</div>
        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td style="width: 55%;">
                    <div class="seller-name">{{ $branch->name }}</div>
                    @if ($branch->address)
                        <div>{{ $branch->address }}</div>
                    @endif
                    @if ($branch->phone)
                        <div>{{ $branch->phone }}</div>
                    @endif
                    @if ($branch->gstin)
                        <div style="margin-top: 4px;"><strong>GSTIN:</strong> {{ $branch->gstin }}</div>
                    @endif
                    @if ($branch->state)
                        <div><strong>State:</strong> {{ $branch->state }}@if ($branchStateCode) (Code {{ $branchStateCode }})@endif</div>
                    @endif
                </td>
                <td class="meta-box">
                    <div><span class="info-label">Invoice #</span><br><strong>{{ $invoice->invoice_number }}</strong></div>
                    <div style="margin-top: 6px;"><span class="info-label">Invoice Date</span><br>{{ $invoice->invoice_date->format('d M Y') }}</div>
                    @if ($customer?->state || $branch->state)
                        <div style="margin-top: 6px;"><span class="info-label">Place of Supply</span><br>
                            {{ $customer?->state ?: $branch->state }}@if ($customerStateCode ?? $branchStateCode) (Code {{ $customerStateCode ?? $branchStateCode }})@endif
                        </div>
                    @endif
                    <div style="margin-top: 6px;"><span class="info-label">Reverse Charge</span><br>No</div>
                </td>
            </tr>
        </table>

        <table class="party-row" style="margin-top: 4px;">
            <tr>
                <td style="width: 48%;">
                    <div class="party-box">
                        <div class="info-label">Bill To</div>
                        <strong>{{ $invoice->displayCustomerName() }}</strong>
                        @if ($customer?->phone)
                            <div>{{ $customer->phone }}</div>
                        @endif
                        @if ($customer?->address)
                            <div>{{ $customer->address }}</div>
                        @endif
                        @if ($customer?->gst_number)
                            <div style="margin-top: 4px;"><strong>GSTIN:</strong> {{ $customer->gst_number }}</div>
                        @endif
                        @if ($customer?->state)
                            <div><strong>State:</strong> {{ $customer->state }}@if ($customerStateCode) (Code {{ $customerStateCode }})@endif</div>
                        @endif
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    <div class="party-box">
                        <div class="info-label">Ship To</div>
                        <div>Same as billing address</div>
                        @if ($invoice->transport_note)
                            <div style="margin-top: 4px;"><strong>Transport:</strong> {{ $invoice->transport_note }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Sr</th>
                    <th style="text-align:left;">Item</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Taxable Value</th>
                    @if ($isSameState)
                        <th colspan="2">CGST</th>
                        <th colspan="2">SGST</th>
                    @else
                        <th colspan="2">IGST</th>
                    @endif
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $index => $line)
                    @php
                        $lineGst = (float) $line->cgst_amount + (float) $line->sgst_amount + (float) $line->igst_amount;
                        $lineTotal = (float) $line->total + $lineGst;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="item-name">{{ $line->item->name }}</td>
                        <td>{{ $line->hsn_code }}</td>
                        <td>{{ rtrim(rtrim((string) $line->quantity, '0'), '.') }} {{ $line->displayUnit() }}</td>
                        <td>₹{{ number_format($line->unit_price, 2) }}</td>
                        <td>₹{{ number_format($line->total, 2) }}</td>
                        @if ($isSameState)
                            <td>{{ number_format(($line->gst_rate ?? 0) / 2, 2) }}%</td>
                            <td>₹{{ number_format($line->cgst_amount, 2) }}</td>
                            <td>{{ number_format(($line->gst_rate ?? 0) / 2, 2) }}%</td>
                            <td>₹{{ number_format($line->sgst_amount, 2) }}</td>
                        @else
                            <td>{{ number_format($line->gst_rate ?? 0, 2) }}%</td>
                            <td>₹{{ number_format($line->igst_amount, 2) }}</td>
                        @endif
                        <td>₹{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">Total</td>
                    <td>₹{{ number_format($subtotal, 2) }}</td>
                    @if ($isSameState)
                        <td></td>
                        <td>₹{{ number_format($totalCgst, 2) }}</td>
                        <td></td>
                        <td>₹{{ number_format($totalSgst, 2) }}</td>
                    @else
                        <td></td>
                        <td>₹{{ number_format($totalIgst, 2) }}</td>
                    @endif
                    <td>₹{{ number_format($subtotal + $totalGst, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="words-box">
            <strong>Amount in Words:</strong> Rupees {{ \App\Support\NumberToWords::convert((int) round($roundedTotal)) }} Only
        </div>

        @if ($hsnSummary->count() > 0)
            <table class="hsn-table">
                <thead>
                    <tr>
                        <th>HSN</th>
                        <th>Taxable Value</th>
                        @if ($isSameState)
                            <th>CGST</th>
                            <th>SGST</th>
                        @else
                            <th>IGST</th>
                        @endif
                        <th>Total Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hsnSummary as $row)
                        <tr>
                            <td>{{ $row['hsn_code'] }}</td>
                            <td>₹{{ number_format($row['taxable'], 2) }}</td>
                            @if ($isSameState)
                                <td>₹{{ number_format($row['cgst'], 2) }}</td>
                                <td>₹{{ number_format($row['sgst'], 2) }}</td>
                            @else
                                <td>₹{{ number_format($row['igst'], 2) }}</td>
                            @endif
                            <td>₹{{ number_format($row['cgst'] + $row['sgst'] + $row['igst'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>₹{{ number_format($hsnSummary->sum('taxable'), 2) }}</td>
                        @if ($isSameState)
                            <td>₹{{ number_format($totalCgst, 2) }}</td>
                            <td>₹{{ number_format($totalSgst, 2) }}</td>
                        @else
                            <td>₹{{ number_format($totalIgst, 2) }}</td>
                        @endif
                        <td>₹{{ number_format($totalGst, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="payment-section">
            <div class="payment-qr">
                <div class="qr-label">Scan &amp; Pay</div>
                <img src="{{ 'data:'.$branch->paymentQrMimeType().';base64,'.base64_encode(file_get_contents($branch->paymentQrPath())) }}" alt="Payment QR">
                <div class="bank-details">
                    <div class="bank-name">{{ $branch->paymentBankName() }}</div>
                    <div>A/c No: {{ $branch->paymentAccountNumber() }}</div>
                    <div>A/c Holder: {{ $branch->paymentAccountHolderName() }}</div>
                    <div>IFSC: {{ $branch->paymentIfscCode() }}</div>
                </div>
            </div>

            <table class="totals">
                <tr>
                    <td class="label">Taxable Value</td>
                    <td class="value">₹{{ number_format($subtotal, 2) }}</td>
                </tr>
                @if ($invoice->discount > 0)
                    <tr>
                        <td class="label">Discount</td>
                        <td class="value">-₹{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                @endif
                @if ($isSameState)
                    <tr>
                        <td class="label">CGST</td>
                        <td class="value">₹{{ number_format($totalCgst, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">SGST</td>
                        <td class="value">₹{{ number_format($totalSgst, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="label">IGST</td>
                        <td class="value">₹{{ number_format($totalIgst, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->transportation > 0)
                    <tr>
                        <td class="label">Transportation</td>
                        <td class="value">₹{{ number_format($invoice->transportation, 2) }}</td>
                    </tr>
                @endif
                @if ($roundOff != 0)
                    <tr>
                        <td class="label">Round Off</td>
                        <td class="value">{{ $roundOff > 0 ? '+' : '' }}₹{{ number_format($roundOff, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand">
                    <td class="label">Total</td>
                    <td class="value">₹{{ number_format($roundedTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Paid</td>
                    <td class="value">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                @if ($invoice->isGuestCustomer())
                    <tr class="grand">
                        <td class="label">{{ $invoice->balanceDue() > 0 ? 'Balance Due' : 'Settled' }}</td>
                        <td class="value {{ $invoice->balanceDue() > 0 ? 'balance-due' : 'balance-paid' }}">
                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                        </td>
                    </tr>
                @else
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
                @endif
            </table>
            <div class="clearfix"></div>
        </div>

        @if ($invoice->notes)
            <div style="margin-top: 16px;">
                <div class="info-label">Notes</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        <div class="terms">
            Certified that the particulars given above are true and correct. This is a computer-generated tax invoice.
        </div>

        <div class="signatory">
            <div class="for-branch">For {{ $branch->name }}</div>
            <div>Authorised Signatory</div>
        </div>

        <div class="footer">Thank you for your business!</div>
    </div>
</body>
</html>
