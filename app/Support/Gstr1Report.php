<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Builds a GSTR-1 (outward supplies) return for one GST-registered branch
 * and one calendar month, from the GST figures already snapshotted on each
 * invoice_items row at billing time — the same numbers the customer's own
 * invoice PDF shows, so this report can never disagree with a bill already
 * handed out.
 *
 * GST registration is per branch (Branch::gstin/state_code), not per
 * company, so a return is always for exactly one branch — combining two
 * branches with different GSTINs into one return would be incorrect even
 * if they belong to the same company.
 *
 * This produces a review-ready report matching the standard GSTR-1 sections
 * (B2B, B2C Large, B2C Small, HSN-wise summary) for a CA/accountant to file
 * from or to re-key into the GST portal — it does not attempt to produce
 * the government offline-tool's exact import file format, which is a
 * narrow, frequently-revised spec better left to dedicated GST software.
 */
class Gstr1Report
{
    /** A B2C invoice goes to B2CL (invoice-wise) instead of B2CS (aggregated) above this value, if inter-state. */
    private const B2CL_THRESHOLD = 250000;

    private const UQC_MAP = [
        'pcs' => 'PCS-PIECES', 'pc' => 'PCS-PIECES', 'piece' => 'PCS-PIECES', 'pieces' => 'PCS-PIECES',
        'nos' => 'NOS-NUMBERS', 'no' => 'NOS-NUMBERS', 'unit' => 'NOS-NUMBERS', 'units' => 'NOS-NUMBERS',
        'kg' => 'KGS-KILOGRAMS', 'kgs' => 'KGS-KILOGRAMS',
        'gm' => 'GMS-GRAMS', 'gram' => 'GMS-GRAMS', 'grams' => 'GMS-GRAMS',
        'ltr' => 'LTR-LITRES', 'litre' => 'LTR-LITRES', 'litres' => 'LTR-LITRES', 'l' => 'LTR-LITRES',
        'ml' => 'MLT-MILLILITRE',
        'box' => 'BOX-BOX', 'boxes' => 'BOX-BOX',
        'bag' => 'BAG-BAGS', 'bags' => 'BAG-BAGS',
        'bundle' => 'BDL-BUNDLES', 'bdl' => 'BDL-BUNDLES',
        'dozen' => 'DOZ-DOZENS', 'dz' => 'DOZ-DOZENS',
        'meter' => 'MTR-METERS', 'metre' => 'MTR-METERS', 'mtr' => 'MTR-METERS', 'm' => 'MTR-METERS',
        'set' => 'SET-SETS', 'sets' => 'SET-SETS',
        'pair' => 'PRS-PAIRS', 'pairs' => 'PRS-PAIRS', 'prs' => 'PRS-PAIRS',
        'roll' => 'ROL-ROLLS', 'rolls' => 'ROL-ROLLS',
        'ton' => 'TON-TONNES', 'tonne' => 'TON-TONNES', 'tonnes' => 'TON-TONNES',
        'carton' => 'CTN-CARTONS', 'ctn' => 'CTN-CARTONS',
    ];

    public static function build(Branch $branch, int $year, int $month): array
    {
        $invoices = Invoice::query()
            ->where('branch_id', $branch->id)
            ->where('is_gst_invoice', true)
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->with(['customer', 'items'])
            ->orderBy('invoice_date')
            ->orderBy('invoice_number')
            ->get();

        $b2b = collect();
        $b2cl = collect();
        $b2cs = collect();
        $hsn = collect();
        $warnings = collect();

        foreach ($invoices as $invoice) {
            $igstTotal = (float) $invoice->items->sum('igst_amount');
            $isInterState = $igstTotal > 0;
            $customerGstin = $invoice->customer?->gst_number;
            $isB2b = filled($customerGstin);

            $placeOfSupply = $isInterState
                ? ($invoice->customer?->state_code ?: null)
                : $branch->state_code;

            if ($isInterState && ! $placeOfSupply) {
                $warnings->push(__('Invoice :number is inter-state but its customer has no state on file — place of supply could not be determined.', ['number' => $invoice->invoice_number]));
            }

            $byRate = $invoice->items->groupBy(fn ($line) => (string) $line->gst_rate);

            if ($isB2b) {
                foreach ($byRate as $rate => $lines) {
                    $b2b->push(self::rateRow($invoice, (float) $rate, $lines, $placeOfSupply, $customerGstin));
                }
            } elseif ($isInterState && (float) $invoice->total > self::B2CL_THRESHOLD) {
                foreach ($byRate as $rate => $lines) {
                    $b2cl->push(self::rateRow($invoice, (float) $rate, $lines, $placeOfSupply, null));
                }
            } else {
                foreach ($byRate as $rate => $lines) {
                    $key = $placeOfSupply.'|'.$rate;
                    $taxable = (float) $lines->sum('total');
                    $cgst = (float) $lines->sum('cgst_amount');
                    $sgst = (float) $lines->sum('sgst_amount');
                    $igst = (float) $lines->sum('igst_amount');

                    $existing = $b2cs->get($key, [
                        'place_of_supply' => $placeOfSupply,
                        'rate' => (float) $rate,
                        'taxable_value' => 0.0,
                        'cgst' => 0.0,
                        'sgst' => 0.0,
                        'igst' => 0.0,
                    ]);
                    $existing['taxable_value'] += $taxable;
                    $existing['cgst'] += $cgst;
                    $existing['sgst'] += $sgst;
                    $existing['igst'] += $igst;
                    $b2cs->put($key, $existing);
                }
            }

            foreach ($invoice->items as $line) {
                $hsnCode = $line->hsn_code ?: __('(no HSN set)');
                $uqc = self::uqcFor($line->displayUnit());
                $key = $hsnCode.'|'.$line->gst_rate.'|'.$uqc;

                $row = $hsn->get($key, [
                    'hsn_code' => $hsnCode,
                    'uqc' => $uqc,
                    'rate' => (float) $line->gst_rate,
                    'quantity' => 0.0,
                    'taxable_value' => 0.0,
                    'cgst' => 0.0,
                    'sgst' => 0.0,
                    'igst' => 0.0,
                ]);
                $row['quantity'] += (float) $line->base_quantity;
                $row['taxable_value'] += (float) $line->total;
                $row['cgst'] += (float) $line->cgst_amount;
                $row['sgst'] += (float) $line->sgst_amount;
                $row['igst'] += (float) $line->igst_amount;
                $hsn->put($key, $row);
            }
        }

        $allRows = $b2b->concat($b2cl)->concat($b2cs->values());
        $summary = [
            'taxable_value' => round((float) $allRows->sum('taxable_value'), 2),
            'cgst' => round((float) $allRows->sum('cgst'), 2),
            'sgst' => round((float) $allRows->sum('sgst'), 2),
            'igst' => round((float) $allRows->sum('igst'), 2),
            'invoice_count' => $invoices->count(),
        ];
        $summary['total_tax'] = round($summary['cgst'] + $summary['sgst'] + $summary['igst'], 2);
        $summary['invoice_value'] = round($summary['taxable_value'] + $summary['total_tax'], 2);

        return [
            'b2b' => $b2b->values(),
            'b2cl' => $b2cl->values(),
            'b2cs' => $b2cs->values()->sortBy(['place_of_supply', 'rate'])->values(),
            'hsn' => $hsn->values()->sortBy(['hsn_code', 'rate'])->values(),
            'summary' => $summary,
            'warnings' => $warnings,
            'documentSummary' => [
                'from' => $invoices->first()?->invoice_number,
                'to' => $invoices->last()?->invoice_number,
                'count' => $invoices->count(),
            ],
        ];
    }

    private static function rateRow(Invoice $invoice, float $rate, Collection $lines, ?string $placeOfSupply, ?string $gstin): array
    {
        return [
            'gstin' => $gstin,
            'customer_name' => $invoice->customer?->name ?? $invoice->guest_customer_name,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'invoice_value' => (float) $invoice->total,
            'place_of_supply' => $placeOfSupply,
            'rate' => $rate,
            'taxable_value' => (float) $lines->sum('total'),
            'cgst' => (float) $lines->sum('cgst_amount'),
            'sgst' => (float) $lines->sum('sgst_amount'),
            'igst' => (float) $lines->sum('igst_amount'),
        ];
    }

    private static function uqcFor(?string $unit): string
    {
        $key = strtolower(trim((string) $unit));

        return self::UQC_MAP[$key] ?? 'OTH-OTHERS';
    }
}
