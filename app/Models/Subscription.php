<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    public const STATUS_TRIALING = 'trialing';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_INCOMPLETE = 'incomplete';

    protected $fillable = [
        'company_id',
        'plan_id',
        'term_years',
        'status',
        'is_lifetime',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'razorpay_subscription_id',
        'razorpay_customer_id',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_lifetime' => 'boolean',
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isOnTrial(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        if ($this->is_lifetime) {
            return true;
        }

        return in_array($this->status, [self::STATUS_TRIALING, self::STATUS_ACTIVE], true)
            && ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        if ($this->is_lifetime) {
            return false;
        }

        if ($this->status === self::STATUS_TRIALING) {
            return $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
        }

        // There is no scheduled job that flips an ACTIVE subscription to
        // PAST_DUE once its paid period lapses, so this date check is the
        // only thing that actually catches "paid plan ran out and nobody
        // renewed" — without it, isActive() would stay true forever.
        if ($this->status === self::STATUS_ACTIVE) {
            return $this->current_period_end !== null && $this->current_period_end->isPast();
        }

        return in_array($this->status, [self::STATUS_PAST_DUE, self::STATUS_CANCELED], true);
    }

    /**
     * The date this subscription stopped being valid, for display — null
     * when there's nothing meaningful to show (e.g. never activated).
     */
    public function expiredAt(): ?\Illuminate\Support\Carbon
    {
        return match ($this->status) {
            self::STATUS_TRIALING => $this->trial_ends_at,
            self::STATUS_ACTIVE => $this->current_period_end,
            self::STATUS_CANCELED => $this->cancelled_at,
            default => null,
        };
    }

    public function daysLeftInTrial(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false) + 1;
    }
}
