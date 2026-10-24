<?php

/**
 * Run this ON YOUR LIVE SERVER, from the project root:
 *
 *   php diag_profit.php
 *
 * Shows, for TODAY's invoices (same window as the dashboard's "Today"
 * card), every billed line sorted by the most negative profit first, so
 * you can see exactly which item/invoice is dragging the Profit card
 * into the red and why (which cost source it used).
 *
 * Optional: pass a branch id to check a specific branch only, e.g.
 *   php diag_profit.php 2
 *
 * Read-only — this script never changes anything. Delete it from the
 * server once you're done with it.
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$branchId = isset($argv[1]) ? (int) $argv[1] : null;

$today = now()->toDateString();

$query = DB::table('invoice_items as ii')
    ->join('invoices as inv', 'inv.id', '=', 'ii.invoice_id')
    ->join('items as it', 'it.id', '=', 'ii.item_id')
    ->whereDate('inv.invoice_date', $today)
    ->when($branchId, fn ($q) => $q->where('inv.branch_id', $branchId))
    ->select(
        'inv.invoice_number', 'inv.branch_id', 'it.name as item_name',
        'ii.quantity', 'ii.unit', 'ii.base_quantity', 'ii.unit_price', 'ii.total',
        'ii.stock_movement_id', 'it.purchase_price',
        DB::raw('(SELECT SUM(sba.quantity * sb.unit_cost)
                   FROM stock_batch_allocations sba
                   JOIN stock_batches sb ON sb.id = sba.stock_batch_id
                   WHERE sba.stock_movement_id = ii.stock_movement_id) as fifo_cost')
    )
    ->orderBy('ii.id')
    ->get();

$rows = [];

foreach ($query as $r) {
    $fifoCost = $r->fifo_cost !== null ? (float) $r->fifo_cost : null;
    $fallbackCost = (float) $r->base_quantity * (float) $r->purchase_price;
    $costUsed = $fifoCost ?? $fallbackCost;
    $profit = round((float) $r->total - $costUsed, 2);

    $rows[] = [
        'invoice' => $r->invoice_number,
        'branch' => $r->branch_id,
        'item' => $r->item_name,
        'qty' => $r->quantity.' '.($r->unit ?? ''),
        'sold_for' => (float) $r->total,
        'cost_source' => $fifoCost !== null ? 'FIFO batch' : 'fallback (purchase_price)',
        'cost' => round($costUsed, 2),
        'profit' => $profit,
    ];
}

usort($rows, fn ($a, $b) => $a['profit'] <=> $b['profit']);

echo "=== Today's invoice lines, most negative profit first ===\n\n";

$totalProfit = 0;
foreach ($rows as $row) {
    $totalProfit += $row['profit'];
    printf(
        "%-18s branch=%-3s %-25s qty=%-10s sold=%10.2f  cost=%10.2f (%s)  profit=%10.2f\n",
        $row['invoice'], $row['branch'], mb_substr($row['item'], 0, 25), $row['qty'],
        $row['sold_for'], $row['cost'], $row['cost_source'], $row['profit']
    );
}

echo "\n==========================================\n";
printf("Total lines: %d   Total profit: %.2f\n", count($rows), $totalProfit);
echo "\nLook at the most negative rows above first — check if their 'cost' looks\n";
echo "right for that item (especially ones using 'fallback (purchase_price)',\n";
echo "since that's the item master's current purchase price, not what was\n";
echo "actually paid for the batch that was sold).\n";
