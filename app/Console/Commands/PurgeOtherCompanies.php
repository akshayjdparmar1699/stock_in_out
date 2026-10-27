<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\StaffMember;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off: keeps exactly one company (by default, id 1 — Om Sai
 * Aallubhandar) and permanently deletes every other company plus
 * everything that belongs to it: branches, items, suppliers, customers,
 * staff members, users, invoices/purchases (and their line items and
 * payments, via DB cascade), subscriptions and role permissions.
 *
 * The Plan catalog and the leads table are platform-wide, not
 * per-company, so neither is touched.
 *
 * Deletion order respects the schema's restrictOnDelete constraints
 * (invoices/purchases on branch_id, items/suppliers/branches on
 * company_id): children are removed before the parent they restrict.
 * Customers and staff members have no company/branch column of their
 * own — they're only linked via the branch_customer / branch_staff_member
 * pivots — so they're identified as orphaned (no remaining link to any
 * surviving branch) after the branch cascade runs, not by a direct
 * company match.
 *
 * Always run with --dry-run first and read the counts before --force.
 */
#[Signature('app:purge-other-companies {--keep=1 : Company ID to keep} {--dry-run : Only show what would be deleted} {--force : Actually delete (required unless --dry-run)}')]
#[Description('Delete every company except the one kept, and all of its related data')]
class PurgeOtherCompanies extends Command
{
    public function handle(): int
    {
        $keepId = (int) $this->option('keep');
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('force')) {
            $this->error('Pass --dry-run to preview, or --force to actually delete.');

            return self::FAILURE;
        }

        $keepCompany = Company::find($keepId);

        if (! $keepCompany) {
            $this->error("Company id {$keepId} does not exist — aborting.");

            return self::FAILURE;
        }

        $this->info("Keeping company #{$keepCompany->id} — {$keepCompany->name}");

        $otherCompanyIds = Company::where('id', '!=', $keepId)->pluck('id');
        $otherBranchIds = Branch::where('company_id', '!=', $keepId)->orWhereNull('company_id')->pluck('id');
        $keepBranchIds = Branch::where('company_id', $keepId)->pluck('id');

        $this->table(['Scope', 'Count'], [
            ['Companies to delete', $otherCompanyIds->count()],
            ['Branches to delete', $otherBranchIds->count()],
            ['Invoices to delete', Invoice::whereIn('branch_id', $otherBranchIds)->count()],
            ['Purchases to delete', Purchase::whereIn('branch_id', $otherBranchIds)->count()],
            ['Items to delete', Item::whereIn('company_id', $otherCompanyIds)->count()],
            ['Suppliers to delete', Supplier::whereIn('company_id', $otherCompanyIds)->count()],
            ['Users to delete', User::whereIn('company_id', $otherCompanyIds)->count()],
            ['Customers to delete (no tie to a kept branch)', $this->orphanedCustomers($keepBranchIds)->count()],
            ['Staff members to delete (no tie to a kept branch)', $this->orphanedStaff($keepBranchIds)->count()],
        ]);

        if ($dryRun) {
            $this->warn('Dry run only — nothing was deleted.');

            return self::SUCCESS;
        }

        if (! $this->confirm("This permanently deletes {$otherCompanyIds->count()} compan(y/ies) and all related data. Have you already taken a full database backup?", false)) {
            $this->warn('Aborted — take a backup first.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($keepId, $otherCompanyIds, $otherBranchIds, $keepBranchIds) {
            Invoice::whereIn('branch_id', $otherBranchIds)->delete();
            Purchase::whereIn('branch_id', $otherBranchIds)->delete();

            // Cascades: item_stocks, stock_movements (-> stock_batches ->
            // stock_batch_allocations), branch_customer, branch_staff_member,
            // expenses, app_notifications, branch_supplier. Nulls users.branch_id.
            Branch::whereIn('id', $otherBranchIds)->delete();

            Item::whereIn('company_id', $otherCompanyIds)->delete();
            Supplier::whereIn('company_id', $otherCompanyIds)->delete();

            $this->orphanedCustomers($keepBranchIds)->get()->each->delete();
            $this->orphanedStaff($keepBranchIds)->get()->each->delete();

            User::whereIn('company_id', $otherCompanyIds)->delete();

            // Cascades: subscriptions (-> subscription_payments), role_permissions.
            Company::whereIn('id', $otherCompanyIds)->delete();
        });

        $this->info("Done — only company #{$keepId} remains.");

        return self::SUCCESS;
    }

    /**
     * A customer with no invoice on, and no branch link to, any kept branch
     * has nothing tying it to the surviving company — true both before the
     * other companies' branches/invoices are deleted (dry-run preview) and
     * after (the real run), since it never depends on what's already gone.
     */
    private function orphanedCustomers(\Illuminate\Support\Collection $keepBranchIds)
    {
        return Customer::whereDoesntHave('branches', fn ($q) => $q->whereIn('branches.id', $keepBranchIds))
            ->whereDoesntHave('invoices', fn ($q) => $q->whereIn('invoices.branch_id', $keepBranchIds));
    }

    private function orphanedStaff(\Illuminate\Support\Collection $keepBranchIds)
    {
        return StaffMember::whereDoesntHave('branches', fn ($q) => $q->whereIn('branches.id', $keepBranchIds))
            ->whereDoesntHave('expenses', fn ($q) => $q->whereIn('expenses.branch_id', $keepBranchIds));
    }
}
