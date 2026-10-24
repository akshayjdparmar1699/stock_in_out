<?php

namespace App\Support;

use App\Models\Invoice;

/**
 * Adapts a real Invoice (with its branch, customer and items loaded) into
 * the generic business/invoice/customer/items shape the 5 selectable
 * invoice formats expect — they're written against plain field names
 * (data_get()-able) rather than this app's own Eloquent attribute names, so
 * they can be dropped in with no changes of their own.
 */
class InvoiceFormatData
{
    public static function build(Invoice $invoice, bool $forPdf = false): array
    {
        $branch = $invoice->branch;
        $customer = $invoice->customer;

        // Same snapshot-based same-state/inter-state check the app's own
        // invoice PDF uses — cgst_amount is null only on an inter-state
        // line, so this reflects what was actually true at billing time,
        // not whatever the customer's state happens to say today.
        $isGst = (bool) $invoice->is_gst_invoice;
        $isSameState = $invoice->items->contains(fn ($line) => $line->cgst_amount !== null);
        $isInterState = $isGst && ! $isSameState;
        $sellerStateCode = (string) $branch->state_code;
        $supplyStateCode = $isInterState
            ? ($customer?->state_code && $customer->state_code !== $sellerStateCode ? $customer->state_code : 'OS')
            : $sellerStateCode;

        $qr = null;
        if ($branch->hasPaymentDetails()) {
            $qr = $forPdf
                ? 'data:'.$branch->paymentQrMimeType().';base64,'.base64_encode(file_get_contents($branch->paymentQrPath()))
                : $branch->paymentQrUrl();
        }

        $business = (object) [
            'name' => $branch->name,
            'address' => $branch->address,
            'phone' => $branch->phone,
            'gstin' => $branch->gstin,
            'state_name' => $branch->state,
            'state_code' => $sellerStateCode,
            'logo_path' => null,
            'bank_name' => $branch->paymentBankName(),
            'account_no' => $branch->paymentAccountNumber(),
            'account_holder' => $branch->paymentAccountHolderName(),
            'ifsc' => $branch->paymentIfscCode(),
            'upi_qr' => $qr,
            'terms' => $branch->company?->invoice_terms,
        ];

        $previousDue = $customer
            ? round((float) $customer->dueAmount() - ((float) $invoice->total - (float) $invoice->paid_amount), 2)
            : 0.0;

        $invoiceData = (object) [
            'number' => $invoice->invoice_number,
            'date' => $invoice->invoice_date,
            'is_gst' => $isGst,
            'rates_include_gst' => false,
            'place_of_supply_name' => $customer?->state ?: $branch->state,
            'place_of_supply_code' => $supplyStateCode,
            'reverse_charge' => false,
            'payment_mode' => $invoice->payments->first()?->mode,
            'status' => match ($invoice->status) {
                'paid' => 'Paid',
                'partial' => 'Partly paid',
                default => 'Unpaid',
            },
            'received' => (float) $invoice->paid_amount,
            'previous_due' => $previousDue,
            'discount' => (float) $invoice->discount,
            'transport' => (float) $invoice->transportation,
            'note' => $invoice->notes,
            'ship_to_name' => null,
            'ship_to_address' => null,
        ];

        $customerData = (object) [
            'name' => $invoice->displayCustomerName(),
            'address' => $customer?->address,
            'phone' => $customer?->phone,
            'gstin' => $customer?->gst_number,
        ];

        $items = $invoice->items->map(fn ($line) => (object) [
            'name' => $line->item->name,
            'hsn' => $line->hsn_code,
            'qty' => (float) $line->quantity,
            'unit' => $line->displayUnit(),
            'rate' => (float) $line->unit_price,
            'gst_rate' => (float) ($line->gst_rate ?? 0),
        ]);

        return [
            'business' => $business,
            'invoice' => $invoiceData,
            'customer' => $customerData,
            'items' => $items,
            'accent' => InvoiceFormat::accentFor($invoice, $branch),
            'forPdf' => $forPdf,
        ];
    }
}
