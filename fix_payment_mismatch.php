<?php

/**
 * Run this ON YOUR LIVE SERVER, from the project root:
 *
 *   php fix_payment_mismatch.php
 *
 * That only SCANS and PRINTS a report — it changes nothing.
 *
 * Once you've read the report and want it to actually correct the bad
 * rows (set paid_amount back to what was really received/paid, and fix
 * the status to match), run it again with --apply:
 *
 *   php fix_payment_mismatch.php --apply
 *
 * Delete this file from the server once you're done with it.
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$apply = in_array('--apply', $argv, true);

echo $apply
    ? "=== APPLY MODE: mismatched rows will be corrected ===\n\n"
    : "=== DRY RUN: nothing will be changed. Re-run with --apply to fix. ===\n\n";

function deriveStatus(float $paid, float $total): string
{
    if ($total > 0 && $paid >= $total - 0.004) {
        return 'paid';
    }

    return $paid > 0.004 ? 'partial' : 'unpaid';
}

$totalMismatches = 0;

// ---------------------------------------------------------------
// INVOICES
// ---------------------------------------------------------------
echo "--- Invoices ---\n";

$invoiceRows = DB::table('invoices as i')
    ->leftJoin('invoice_payments as p', 'p.invoice_id', '=', 'i.id')
    ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
    ->select(
        'i.id', 'i.invoice_number', 'i.total', 'i.paid_amount', 'i.status',
        'c.name as customer_name', 'i.guest_customer_name',
        DB::raw('COALESCE(SUM(p.amount), 0) as real_received')
    )
    ->groupBy('i.id', 'i.invoice_number', 'i.total', 'i.paid_amount', 'i.status', 'c.name', 'i.guest_customer_name')
    ->get();

foreach ($invoiceRows as $row) {
    $storedPaid = round((float) $row->paid_amount, 2);
    $realPaid = round((float) $row->real_received, 2);

    if (abs($storedPaid - $realPaid) < 0.01) {
        continue;
    }

    $totalMismatches++;
    $correctStatus = deriveStatus($realPaid, (float) $row->total);
    $customer = $row->customer_name ?? $row->guest_customer_name ?? '(no customer)';

    printf(
        "MISMATCH  %s  (%s)  total=%.2f  stored paid_amount=%.2f (%s)  -> real received=%.2f (%s)\n",
        $row->invoice_number, $customer, (float) $row->total,
        $storedPaid, $row->status, $realPaid, $correctStatus
    );

    if ($apply) {
        DB::table('invoices')->where('id', $row->id)->update([
            'paid_amount' => $realPaid,
            'status' => $correctStatus,
        ]);
    }
}

// ---------------------------------------------------------------
// PURCHASES
// ---------------------------------------------------------------
echo "\n--- Purchases ---\n";

$purchaseRows = DB::table('purchases as pu')
    ->leftJoin('purchase_payments as pp', 'pp.purchase_id', '=', 'pu.id')
    ->leftJoin('suppliers as s', 's.id', '=', 'pu.supplier_id')
    ->select(
        'pu.id', 'pu.purchase_number', 'pu.total', 'pu.paid_amount', 'pu.status',
        's.name as supplier_name',
        DB::raw('COALESCE(SUM(pp.amount), 0) as real_paid')
    )
    ->groupBy('pu.id', 'pu.purchase_number', 'pu.total', 'pu.paid_amount', 'pu.status', 's.name')
    ->get();

foreach ($purchaseRows as $row) {
    $storedPaid = round((float) $row->paid_amount, 2);
    $realPaid = round((float) $row->real_paid, 2);

    if (abs($storedPaid - $realPaid) < 0.01) {
        continue;
    }

    $totalMismatches++;
    $correctStatus = deriveStatus($realPaid, (float) $row->total);

    printf(
        "MISMATCH  %s  (%s)  total=%.2f  stored paid_amount=%.2f (%s)  -> real paid=%.2f (%s)\n",
        $row->purchase_number, $row->supplier_name ?? '(no supplier)', (float) $row->total,
        $storedPaid, $row->status, $realPaid, $correctStatus
    );

    if ($apply) {
        DB::table('purchases')->where('id', $row->id)->update([
            'paid_amount' => $realPaid,
            'status' => $correctStatus,
        ]);
    }
}

echo "\n==========================================\n";
echo "Total mismatched rows found: {$totalMismatches}\n";
echo $apply ? "All of the above were corrected.\n" : "Nothing changed. Re-run with --apply to fix them.\n";
