<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        :root {
            --accent: #6a00d9;
            --border: #1f2937;
            --border-light: #9ca3af;
            --muted: #6b7280;
            --text: #1f2937;
        }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: var(--text); margin: 0; padding: 18px; }
        .invoice-box { border: 1.5px solid var(--border); padding: 18px; }
        table { width: 100%; border-collapse: collapse; }
        .title-row td { vertical-align: middle; }
        .tax-invoice-title { font-size: 18px; font-weight: bold; letter-spacing: .04em; }
        .original-box { display: inline-block; border: 1px solid var(--border); padding: 4px 10px; font-size: 9px; font-weight: bold; letter-spacing: .03em; }
        .divider { border-top: 1.5px solid var(--border); margin: 10px 0; }
        .info-table td { vertical-align: top; }
        .info-label { color: var(--muted); font-size: 9px; text-transform: uppercase; margin-bottom: 2px; }
        .seller-logo { width: 38px; height: 38px; border-radius: 8px; }
        .seller-name { font-size: 15px; font-weight: bold; margin: 0 0 2px 0; }
        .meta-grid { border: 1px solid var(--border); width: 100%; margin-bottom: 6px; }
        .meta-grid td { padding: 4px 8px; width: 50%; }
        .meta-grid td + td { border-left: 1px solid var(--border); }
        .party-box { border: 1px solid var(--border); padding: 6px 10px; width: 48%; }
        .party-row td { vertical-align: top; }
        .items-table { border: 1px solid var(--border); margin-top: 10px; font-size: 10px; }
        .items-table th { background: #f3f4f6; text-align: center; padding: 5px 4px; font-size: 9px; text-transform: uppercase; color: var(--muted); border: 1px solid var(--border); }
        .items-table td { padding: 4px 4px; border: 1px solid var(--border-light); text-align: center; }
        .items-table td.item-name { text-align: left; }
        .items-table tbody tr { page-break-inside: avoid; }
        .items-table tfoot td { font-weight: bold; background: #f9fafb; border: 1px solid var(--border); }
        .hsn-table { border: 1px solid var(--border); margin-top: 10px; font-size: 10px; }
        .hsn-table th { background: #f3f4f6; text-align: center; padding: 5px 4px; font-size: 9px; text-transform: uppercase; color: var(--muted); border: 1px solid var(--border); }
        .hsn-table td { padding: 4px 4px; border: 1px solid var(--border-light); text-align: center; }
        .hsn-table tbody tr { page-break-inside: avoid; }
        .hsn-table tfoot td { font-weight: bold; background: #f9fafb; border: 1px solid var(--border); }
        .words-box { margin-top: 8px; border: 1px solid var(--border-light); padding: 6px 10px; font-size: 10.5px; }
        .totals { width: 260px; float: right; margin-top: 12px; }
        .totals td { padding: 3px 8px; }
        .totals .label { color: #4b5563; }
        .totals .value { text-align: right; }
        .totals .grand { font-size: 13px; font-weight: bold; border-top: 1px solid #d1d5db; }
        .balance-due { color: #dc2626; }
        .balance-paid { color: #16a34a; }
        .payment-section { margin-top: 10px; }
        .payment-qr { float: left; width: 200px; }
        .payment-qr img { width: 100px; height: 100px; }
        .payment-qr .qr-label { color: var(--muted); font-size: 9px; text-transform: uppercase; letter-spacing: .03em; margin-bottom: 6px; }
        .payment-qr .bank-details { margin-top: 6px; font-size: 10px; color: #4b5563; line-height: 1.5; white-space: nowrap; }
        .payment-qr .bank-details .bank-name { font-weight: bold; color: var(--text); }
        .clearfix { clear: both; }
        .terms { margin-top: 12px; font-size: 9.5px; color: var(--muted); }
        .signatory { margin-top: 24px; text-align: right; font-size: 10.5px; }
        .signatory .for-branch { font-weight: bold; margin-bottom: 26px; }
        .footer { margin-top: 16px; text-align: center; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $isSameState = $invoice->items->contains(fn ($line) => $line->cgst_amount !== null);
        $branch = $invoice->branch;
        $company = $branch->company;
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

        // The payment mode captured at billing time (if any) — an invoice
        // can gain further payments later with their own modes, but this is
        // the one that best represents "how this bill was paid".
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

        $itemColumnCount = $invoice->is_gst_invoice
            ? 7 + ($isSameState ? 4 : 2)
            : 5;
        $fillerRowCount = max(0, 3 - $invoice->items->count());

        $termsLines = $company?->invoice_terms
            ? collect(explode("\n", $company->invoice_terms))->map(fn ($line) => trim($line))->filter()->values()
            : collect();
    @endphp

    <div class="invoice-box">
        <table class="title-row">
            <tr>
                <td style="text-align: left;"><span class="tax-invoice-title">{{ $invoice->is_gst_invoice ? 'TAX INVOICE' : 'INVOICE' }}</span></td>
                <td style="text-align: right;"><span class="original-box">ORIGINAL FOR RECIPIENT</span></td>
            </tr>
        </table>
        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td style="width: 58%;">
                    <table>
                        <tr>
                            <td style="width: 46px; vertical-align: top;">
                                <img class="seller-logo" src="{{ public_path('icons/icon-192.png') }}" alt="">
                            </td>
                            <td style="vertical-align: top;">
                                <div class="seller-name">{{ $branch->name }}</div>
                                @if ($branch->address || $branch->state)
                                    <div>{{ trim(($branch->address ?: '').($branch->address && $branch->state ? ', ' : '').($branch->state ?: ''), ', ') }}</div>
                                @endif
                                @if ($branch->phone)
                                    <div>{{ $branch->phone }}</div>
                                @endif
                                @if ($invoice->is_gst_invoice && ($branch->gstin || $branch->state))
                                    <div style="margin-top: 4px;">
                                        @if ($branch->gstin)
                                            <strong>GSTIN:</strong> {{ $branch->gstin }}
                                        @endif
                                        @if ($branch->gstin && $branch->state) &middot; @endif
                                        @if ($branch->state)
                                            <strong>State:</strong> {{ $branch->state }}@if ($branchStateCode) ({{ $branchStateCode }})@endif
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 42%; vertical-align: top;">
                    <table class="meta-grid">
                        <tr>
                            <td><span class="info-label">Invoice No.</span><br><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td><span class="info-label">Date</span><br>{{ $invoice->invoice_date->format('d M Y') }}</td>
                        </tr>
                    </table>
                    @if ($invoice->is_gst_invoice)
                        <table class="meta-grid">
                            <tr>
                                <td><span class="info-label">Place of Supply</span><br>
                                    @if ($customer?->state || $branch->state)
                                        {{ $customer?->state ?: $branch->state }}@if ($customerStateCode ?? $branchStateCode) ({{ $customerStateCode ?? $branchStateCode }})@endif
                                    @endif
                                </td>
                                <td><span class="info-label">Reverse Charge</span><br>No</td>
                            </tr>
                        </table>
                    @else
                        <table class="meta-grid">
                            <tr>
                                <td><span class="info-label">Payment Mode</span><br>{{ $paymentModeLabel }}</td>
                                <td><span class="info-label">Status</span><br>{{ $statusLabel }}</td>
                            </tr>
                        </table>
                    @endif
                </td>
            </tr>
        </table>

        <table class="party-row" style="margin-top: 10px;">
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
                        @if ($invoice->is_gst_invoice && $customer?->gst_number)
                            <div style="margin-top: 4px;"><strong>GSTIN:</strong> {{ $customer->gst_number }}</div>
                        @endif
                        @if ($invoice->is_gst_invoice && $customer?->state)
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
                    <th {{ $invoice->is_gst_invoice ? 'rowspan=2' : '' }}>#</th>
                    <th {{ $invoice->is_gst_invoice ? 'rowspan=2' : '' }} style="text-align:left;">Item</th>
                    @if ($invoice->is_gst_invoice)
                        <th rowspan="2">HSN</th>
                    @endif
                    <th {{ $invoice->is_gst_invoice ? 'rowspan=2' : '' }}>Qty</th>
                    <th {{ $invoice->is_gst_invoice ? 'rowspan=2' : '' }}>Rate</th>
                    <th {{ $invoice->is_gst_invoice ? 'rowspan=2' : '' }}>{{ $invoice->is_gst_invoice ? 'Taxable' : 'Amount' }}</th>
                    @if ($invoice->is_gst_invoice)
                        @if ($isSameState)
                            <th colspan="2">CGST</th>
                            <th colspan="2">SGST</th>
                        @else
                            <th colspan="2">IGST</th>
                        @endif
                        <th rowspan="2">Total</th>
                    @endif
                </tr>
                @if ($invoice->is_gst_invoice)
                    <tr>
                        <th>%</th>
                        <th>Amt</th>
                        @if ($isSameState)
                            <th>%</th>
                            <th>Amt</th>
                        @endif
                    </tr>
                @endif
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
                        @if ($invoice->is_gst_invoice)
                            <td>{{ $line->hsn_code }}</td>
                        @endif
                        <td>{{ rtrim(rtrim((string) $line->quantity, '0'), '.') }} {{ $line->displayUnit() }}</td>
                        <td>₹{{ number_format($line->unit_price, 2) }}</td>
                        <td>₹{{ number_format($line->total, 2) }}</td>
                        @if ($invoice->is_gst_invoice)
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
                        @endif
                    </tr>
                @endforeach
                @for ($i = 0; $i < $fillerRowCount; $i++)
                    <tr>
                        @for ($c = 0; $c < $itemColumnCount; $c++)
                            <td>&nbsp;</td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $invoice->is_gst_invoice ? 5 : 4 }}">Total</td>
                    <td>₹{{ number_format($subtotal, 2) }}</td>
                    @if ($invoice->is_gst_invoice)
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
                    @endif
                </tr>
            </tfoot>
        </table>

        <div class="words-box">
            <strong>Amount in Words:</strong> Rupees {{ \App\Support\NumberToWords::convert((int) round($invoice->is_gst_invoice ? $roundedTotal : $invoice->total)) }} Only
        </div>

        @if ($invoice->is_gst_invoice)
            @if ($hsnSummary->count() > 0)
                <table class="hsn-table">
                    <thead>
                        <tr>
                            <th>HSN</th>
                            <th>Taxable</th>
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
        @elseif ($invoice->notes)
            <div class="words-box">
                <div class="info-label">Note</div>
                <div>{{ $invoice->notes }}</div>
            </div>
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
                    <td class="label">{{ $invoice->is_gst_invoice ? 'Taxable value' : 'Subtotal' }}</td>
                    <td class="value">₹{{ number_format($subtotal, 2) }}</td>
                </tr>
                @if ($invoice->discount > 0)
                    <tr>
                        <td class="label">Discount</td>
                        <td class="value">-₹{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->is_gst_invoice)
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
                @elseif ($invoice->tax > 0)
                    <tr>
                        <td class="label">Tax</td>
                        <td class="value">₹{{ number_format($invoice->tax, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->transportation > 0)
                    <tr>
                        <td class="label">Transport</td>
                        <td class="value">₹{{ number_format($invoice->transportation, 2) }}</td>
                    </tr>
                @endif
                @if ($invoice->is_gst_invoice)
                    <tr>
                        <td class="label">Round off</td>
                        <td class="value">{{ $roundOff > 0 ? '+' : '' }}₹{{ number_format($roundOff, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand">
                    <td class="label">Invoice total</td>
                    <td class="value">₹{{ number_format($invoice->is_gst_invoice ? $roundedTotal : $invoice->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Received</td>
                    <td class="value">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                @if ($invoice->isGuestCustomer())
                    <tr class="grand">
                        <td class="label">{{ $invoice->balanceDue() > 0 ? 'Balance due' : 'Settled' }}</td>
                        <td class="value {{ $invoice->balanceDue() > 0 ? 'balance-due' : 'balance-paid' }}">
                            ₹{{ number_format($invoice->balanceDue(), 2) }}
                        </td>
                    </tr>
                @else
                    @php
                        $previousDue = $customerDue - ($invoice->total - $invoice->paid_amount);
                    @endphp
                    <tr>
                        <td class="label" style="border-top:1px solid #d1d5db;padding-top:8px;">Previous due</td>
                        <td class="value {{ $previousDue > 0 ? 'balance-due' : ($previousDue < 0 ? 'balance-paid' : '') }}" style="border-top:1px solid #d1d5db;padding-top:8px;">
                            ₹{{ number_format(abs($previousDue), 2) }}{{ $previousDue < 0 ? ' CR' : '' }}
                        </td>
                    </tr>
                    <tr class="grand">
                        <td class="label">{{ $customerDue > 0 ? 'Balance due' : 'Settled / In Credit' }}</td>
                        <td class="value {{ $customerDue > 0 ? 'balance-due' : 'balance-paid' }}">
                            ₹{{ number_format(abs($customerDue), 2) }}{{ $customerDue < 0 ? ' CR' : '' }}
                        </td>
                    </tr>
                @endif
            </table>
            <div class="clearfix"></div>
        </div>

        @if ($invoice->is_gst_invoice && $invoice->notes)
            <div style="margin-top: 16px;">
                <div class="info-label">Notes</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        <div class="terms">
            @if ($termsLines->isNotEmpty())
                <div class="info-label" style="margin-bottom: 4px;">Terms</div>
                @foreach ($termsLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
            @else
                Certified that the particulars given above are true and correct. This is a computer-generated {{ $invoice->is_gst_invoice ? 'tax invoice' : 'invoice' }}.
            @endif
        </div>

        <div class="signatory">
            <div class="for-branch">For {{ $branch->name }}</div>
            <div>Authorised Signatory</div>
        </div>

        <div class="footer">Thank you for your business!</div>
    </div>
</body>
</html>
