<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'gst_number',
        'opening_balance',
        'credit_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'credit_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    /**
     * Total we owe this supplier: opening due (set when the supplier was
     * added) plus whatever remains unpaid across all purchases from them,
     * minus any credit balance we're currently holding with them (built up
     * from a payment that exceeded everything owed at the time). A negative
     * value means they owe us (we're in credit with them).
     */
    public function dueAmount(): float
    {
        $purchased = (float) $this->purchases()->sum('total');
        $paid = (float) $this->purchases()->sum('paid_amount');

        return round((float) $this->opening_balance + $purchased - $paid - (float) $this->credit_balance, 2);
    }
}
