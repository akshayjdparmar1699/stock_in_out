<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'daily_whatsapp_reports_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'daily_whatsapp_reports_enabled' => 'boolean',
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
}
