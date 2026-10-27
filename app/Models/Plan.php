<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    public const KEY_FREE_TRIAL = 'free_trial';

    public const KEY_SILVER = 'silver';

    public const KEY_GOLD = 'gold';

    public const KEY_PLATINUM = 'platinum';

    /**
     * Multi-year purchase options shown on the renew/upgrade page, and the
     * discount applied when paying for all of them upfront — 30% off the
     * plain 3x total, not 30% off each individual year.
     */
    public const YEARS_OPTIONS = [1, 3];

    public const DISCOUNT_FOR_3_YEARS = 0.30;

    protected $fillable = [
        'key',
        'name',
        'tagline',
        'price',
        'billing_period',
        'trial_days',
        'branch_limit',
        'staff_limit',
        'user_limit',
        'features',
        'is_popular',
        'sort_order',
        'razorpay_plan_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFreeTrial(): bool
    {
        return $this->key === self::KEY_FREE_TRIAL;
    }

    public function hasUnlimitedBranches(): bool
    {
        return $this->branch_limit === null;
    }

    public function hasUnlimitedStaff(): bool
    {
        return $this->staff_limit === null;
    }

    public function hasUnlimitedUsers(): bool
    {
        return $this->user_limit === null;
    }

    /**
     * The total for paying upfront for $years at once — plain price × years
     * for 1 year, or 30% off that total for the 3-year option. Whole
     * rupees throughout, matching how `price` itself is stored.
     */
    public function priceForYears(int $years): int
    {
        if ($years === 3) {
            return (int) round($this->price * 3 * (1 - self::DISCOUNT_FOR_3_YEARS));
        }

        return $this->price;
    }

    /**
     * What that same purchase works out to per month — shown under the
     * yearly price so a buyer can compare it to a monthly expense instead
     * of having to do the division themselves.
     */
    public function monthlyEquivalent(int $years = 1): int
    {
        return (int) round($this->priceForYears($years) / ($years * 12));
    }
}
