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

    protected $fillable = [
        'key',
        'name',
        'tagline',
        'price',
        'billing_period',
        'trial_days',
        'branch_limit',
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
}
