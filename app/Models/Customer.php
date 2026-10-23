<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'gst_number',
        'state',
        'state_code',
        'opening_balance',
        'credit_balance',
        'credit_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'credit_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    /**
     * Total outstanding balance: opening due (set when the customer was
     * created) plus whatever remains unpaid across all their invoices,
     * minus any credit balance they're currently holding (built up from a
     * payment that exceeded everything they owed at the time). A negative
     * value means the customer is in credit.
     */
    public function dueAmount(): float
    {
        $invoiced = (float) $this->invoices()->sum('total');
        $paid = (float) $this->invoices()->sum('paid_amount');

        return round((float) $this->opening_balance + $invoiced - $paid - (float) $this->credit_balance, 2);
    }

    /**
     * Batched dueAmount() for a list of customers at once — two aggregate
     * queries total instead of two queries per customer, for a screen
     * (like the invoices list) that needs many customers' running balance
     * without an N+1 for every row shown.
     */
    public static function dueAmountsFor(iterable $customerIds): \Illuminate\Support\Collection
    {
        $ids = collect($customerIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $invoiceSums = Invoice::whereIn('customer_id', $ids)
            ->selectRaw('customer_id, SUM(total) as total, SUM(paid_amount) as paid')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        return static::query()->whereIn('id', $ids)->get(['id', 'opening_balance', 'credit_balance'])
            ->mapWithKeys(function (self $customer) use ($invoiceSums) {
                $sums = $invoiceSums->get($customer->id);
                $due = round(
                    (float) $customer->opening_balance
                    + (float) ($sums->total ?? 0)
                    - (float) ($sums->paid ?? 0)
                    - (float) $customer->credit_balance,
                    2
                );

                return [$customer->id => $due];
            });
    }

    /**
     * True when a credit limit is set for this customer and their current
     * due has crossed it — a nudge to collect payment before extending
     * them any more credit.
     */
    public function isOverCreditLimit(): bool
    {
        return (float) $this->credit_limit > 0 && $this->dueAmount() > (float) $this->credit_limit;
    }
}
