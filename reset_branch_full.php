<?php

/**
 * Run this ON YOUR LIVE SERVER, from the project root:
 *
 *   php reset_branch_full.php <branch_id>
 *
 * That only scans and PRINTS what it would do — it changes nothing.
 * Once you've reviewed it, run it for real with --apply:
 *
 *   php reset_branch_full.php <branch_id> --apply
 *
 * This supersedes reset_branch_sales.php — it does everything that one
 * did, PLUS clears purchases and all stock for the branch. Don't run
 * both; just this one.
 *
 * What this does for the given branch:
 *   - Deletes every Invoice (items + payments) and every Purchase
 *     (items + payments) for this branch.
 *   - Deletes every stock movement, stock batch and FIFO allocation this
 *     branch has — since nothing was purchased or sold any more, there's
 *     no stock history left to keep. Each item's stock for this branch
 *     is set to exactly 0 (the item_stocks row itself is kept, not
 *     deleted, so every item stays listed and sellable in this branch —
 *     it just starts at 0 until the next purchase).
 *   - Deletes this branch's Expenses and its own notifications.
 *   - For any CUSTOMER linked only to this branch, clears their
 *     leftover credit balance and the unmatched payment rows that built
 *     it up — all of it came from this branch's invoices, now gone.
 *   - For any SUPPLIER who was only ever bought from through this
 *     branch (no purchases in any other branch), same thing: credit
 *     balance and unmatched payment rows cleared.
 *   A customer/supplier linked to another branch too is left completely
 *   untouched either way — their credit may be mixed with that other
 *   branch's activity.
 *
 * Left completely alone: Customers, Suppliers, Items, staff members,
 * partners, and users.
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
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

$branchId = isset($argv[1]) ? (int) $argv[1] : null;
$apply = in_array('--apply', $argv, true);

if (! $branchId) {
    fwrite(STDERR, "Usage: php reset_branch_full.php <branch_id> [--apply]\n");
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
$purchaseCount = 0;
$movementCount = 0;
$itemsZeroed = 0;
$customerCreditReset = 0;
$supplierCreditReset = 0;

$run(function () use (
    $branchId, &$invoiceCount, &$purchaseCount, &$movementCount, &$itemsZeroed,
    &$customerCreditReset, &$supplierCreditReset
) {
    // Every stock movement this branch has, of any kind (purchase-in,
    // sale-out). Deleting each one cascades (via FK) to the stock batch
    // it created and to any FIFO allocation row that points at either
    // that batch or that movement — so this one loop clears every trace
    // of stock activity for the branch.
    $movements = StockMovement::where('branch_id', $branchId)->get();
    $movementCount = $movements->count();
    foreach ($movements as $movement) {
        $movement->delete();
    }

    $invoiceCount = Invoice::where('branch_id', $branchId)->count();
    Invoice::where('branch_id', $branchId)->delete();

    // Which suppliers this branch's (now-deleted) purchases belonged to,
    // checked BEFORE deleting them, so it's known which suppliers have
    // no purchases left anywhere else once this branch's are gone.
    $supplierIdsHere = Purchase::where('branch_id', $branchId)->pluck('supplier_id')->unique();

    $purchaseCount = Purchase::where('branch_id', $branchId)->count();
    Purchase::where('branch_id', $branchId)->delete();

    ItemStock::where('branch_id', $branchId)->where('quantity', '!=', 0)
        ->get()
        ->each(function (ItemStock $stock) use (&$itemsZeroed) {
            $stock->update(['quantity' => 0]);
            $itemsZeroed++;
        });

    $singleBranchCustomerIds = Customer::whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
        ->whereDoesntHave('branches', fn ($q) => $q->where('branches.id', '!=', $branchId))
        ->pluck('id');

    foreach ($singleBranchCustomerIds as $customerId) {
        InvoicePayment::whereNull('invoice_id')->where('customer_id', $customerId)->delete();
        Customer::whereKey($customerId)->update(['credit_balance' => 0]);
        $customerCreditReset++;
    }

    foreach ($supplierIdsHere as $supplierId) {
        $hasPurchasesElsewhere = Purchase::where('supplier_id', $supplierId)
            ->where('branch_id', '!=', $branchId)
            ->exists();

        if (! $hasPurchasesElsewhere) {
            PurchasePayment::whereNull('purchase_id')->where('supplier_id', $supplierId)->delete();
            Supplier::whereKey($supplierId)->update(['credit_balance' => 0]);
            $supplierCreditReset++;
        }
    }

    Expense::where('branch_id', $branchId)->delete();
    AppNotification::where('branch_id', $branchId)->delete();
});

echo "Invoices deleted: {$invoiceCount}\n";
echo "Purchases deleted: {$purchaseCount}\n";
echo "Stock movements deleted (batches + allocations cascade with them): {$movementCount}\n";
echo "Items zeroed out for this branch: {$itemsZeroed}\n";
echo "Single-branch customers reset to 0 credit: {$customerCreditReset}\n";
echo "Single-branch suppliers reset to 0 credit: {$supplierCreditReset}\n";
echo "Expenses and notifications for this branch: deleted\n";
echo "\nUntouched: customers, suppliers, items, staff, partners, users.\n";
echo $apply ? "\nDone.\n" : "\nNothing was actually changed (dry run). Re-run with --apply to do it for real.\n";
