<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'gst_enabled',
        'invoice_terms',
        'default_invoice_payment_status',
        'default_purchase_payment_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gst_enabled' => 'boolean',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function canAddBranch(): bool
    {
        return $this->canAdd('branch_limit', fn () => $this->branches()->count());
    }

    public function canAddStaffMember(): bool
    {
        return $this->canAdd('staff_limit', fn () => StaffMember::whereHas(
            'branches', fn ($q) => $q->where('branches.company_id', $this->id)
        )->count());
    }

    public function canAddUser(): bool
    {
        return $this->canAdd('user_limit', fn () => $this->users()->count());
    }

    /**
     * Fails open (allows the add) whenever there's nothing concrete to
     * check against — a lifetime subscription, no subscription row at all,
     * no plan on the subscription, or a null limit on the plan (the
     * existing "null = unlimited" convention already used by branch_limit).
     * This mirrors the same conservative default already used by
     * CheckSubscriptionActive, rather than locking a company out over an
     * unexpected data gap.
     */
    private function canAdd(string $limitColumn, \Closure $currentCount): bool
    {
        $subscription = $this->subscription;

        if (! $subscription || $subscription->is_lifetime) {
            return true;
        }

        $plan = $subscription->plan;
        $limit = $plan?->{$limitColumn};

        if ($limit === null) {
            return true;
        }

        return $currentCount() < $limit;
    }

    /**
     * Permanently deletes this company and everything that belongs to it —
     * branches, items, suppliers, users, invoices/purchases (and their
     * line items/payments, via DB cascade), subscriptions and role
     * permissions. Customers and staff members have no company/branch
     * column of their own (only the branch_customer/branch_staff_member
     * pivots), so they're deleted only if nothing ties them to any OTHER
     * company's branch — otherwise they're left alone.
     *
     * Deletion order respects the schema's restrictOnDelete constraints
     * (invoices/purchases on branch_id; items/suppliers/branches on
     * company_id), mirroring App\Console\Commands\PurgeOtherCompanies.
     */
    public function eraseEverything(): void
    {
        DB::transaction(function () {
            $branchIds = $this->branches()->pluck('id');
            $otherBranchIds = Branch::where('company_id', '!=', $this->id)->pluck('id');

            Invoice::whereIn('branch_id', $branchIds)->delete();
            Purchase::whereIn('branch_id', $branchIds)->delete();

            // Cascades: item_stocks, stock_movements (-> stock_batches ->
            // stock_batch_allocations), branch_customer, branch_staff_member,
            // expenses, app_notifications, branch_supplier. Nulls users.branch_id.
            Branch::whereIn('id', $branchIds)->delete();

            Item::where('company_id', $this->id)->delete();
            Supplier::where('company_id', $this->id)->delete();

            Customer::whereDoesntHave('branches', fn ($q) => $q->whereIn('branches.id', $otherBranchIds))
                ->whereDoesntHave('invoices', fn ($q) => $q->whereIn('invoices.branch_id', $otherBranchIds))
                ->get()->each->delete();

            StaffMember::whereDoesntHave('branches', fn ($q) => $q->whereIn('branches.id', $otherBranchIds))
                ->whereDoesntHave('expenses', fn ($q) => $q->whereIn('expenses.branch_id', $otherBranchIds))
                ->get()->each->delete();

            User::where('company_id', $this->id)->delete();

            // Cascades: subscriptions (-> subscription_payments), role_permissions.
            $this->delete();
        });
    }
}
