<?php

/**
 * Run this ON YOUR LIVE SERVER, from the project root:
 *
 *   php reset_branch_sales.php <branch_id>
 *
 * That only scans and PRINTS what it would do — it changes nothing.
 * Once you've reviewed it, run it for real with --apply:
 *
 *   php reset_branch_sales.php <branch_id> --apply
 *
 * What this does for the given branch:
 *   - Deletes every Invoice (and its items + payments) billed by this
 *     branch.
 *   - Undoes the stock that those invoices sold — each item's stock goes
 *     back up to exactly what was purchased, as if nothing was ever sold.
 *   - Deletes this branch's Expenses and its own notifications.
 *   - For any customer linked ONLY to this branch (no other branch), also
 *     clears their leftover credit balance and any unmatched payment rows
 *     that credit came from — since 100% of it was built from this
 *     branch's invoices being deleted. A customer who is ALSO linked to
 *     another branch is left completely untouched (their credit may be
 *     mixed with that other branch's activity, which isn't being
 *     touched).
 *
 * Left completely alone: Customers, Suppliers, Purchases (and everything
 * a purchase created: stock-in movements, stock batches), Items, staff
 * members, partners, and users.
 *
 * Delete this file from the server once you're done with it.
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AppNotification;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\ItemStock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

$branchId = isset($argv[1]) ? (int) $argv[1] : null;
$apply = in_array('--apply', $argv, true);

if (! $branchId) {
    fwrite(STDERR, "Usage: php reset_branch_sales.php <branch_id> [--apply]\n");
    exit(1);
}

$branch = Branch::find($branchId);

if (! $branch) {
    fwrite(STDERR, "No branch with id {$branchId} found.\n");
    exit(1);
}

echo "Branch: \"{$branch->name}\" (id {$branch->id})\n";
echo $apply ? "=== APPLY MODE: this will actually delete/reset data ===\n\n" : "=== DRY RUN: nothing will be changed. Re-run with --apply to do it for real. ===\n\n";

$run = function (callable $callback) use ($apply) {
    if ($apply) {
        DB::transaction($callback);
    } else {
        DB::beginTransaction();
        $callback();
        DB::rollBack();
    }
};

$invoiceCount = 0;
$movementCount = 0;
$creditResetCount = 0;

$run(function () use ($branchId, &$invoiceCount, &$movementCount, &$creditResetCount) {
    $invoices = Invoice::where('branch_id', $branchId)->get();
    $invoiceCount = $invoices->count();

    foreach ($invoices as $invoice) {
        $movements = StockMovement::query()
            ->where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->with('batchAllocations')
            ->get();

        foreach ($movements as $movement) {
            $movementCount++;

            foreach ($movement->batchAllocations as $allocation) {
                StockBatch::whereKey($allocation->stock_batch_id)->increment('quantity_remaining', $allocation->quantity);
            }

            ItemStock::query()
                ->where('branch_id', $branchId)
                ->where('item_id', $movement->item_id)
                ->increment('quantity', $movement->quantity);

            $movement->delete();
        }

        // Cascades to invoice_items and to any invoice_payments rows that
        // have this invoice_id set.
        $invoice->delete();
    }

    // Customers linked to exactly this one branch — every rupee of their
    // credit_balance necessarily came from this branch's (now-deleted)
    // invoices, so it's cleared along with the unmatched payment rows
    // that built it up (those have invoice_id null, so deleting invoices
    // above never touched them).
    $singleBranchCustomerIds = Customer::whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
        ->whereDoesntHave('branches', fn ($q) => $q->where('branches.id', '!=', $branchId))
        ->pluck('id');

    foreach ($singleBranchCustomerIds as $customerId) {
        InvoicePayment::whereNull('invoice_id')->where('customer_id', $customerId)->delete();
        Customer::whereKey($customerId)->update(['credit_balance' => 0]);
        $creditResetCount++;
    }

    Expense::where('branch_id', $branchId)->delete();
    AppNotification::where('branch_id', $branchId)->delete();
});

echo "Invoices deleted: {$invoiceCount}\n";
echo "Stock-out movements reversed: {$movementCount}\n";
echo "Single-branch customers reset to 0 credit: {$creditResetCount}\n";
echo "Expenses and notifications for this branch: deleted\n";
echo "\nUntouched: customers, suppliers, purchases (and their stock-in), items, staff, partners, users.\n";
echo $apply ? "\nDone.\n" : "\nNothing was actually changed (dry run). Re-run with --apply to do it for real.\n";
