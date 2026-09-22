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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Total we owe this supplier: opening due (set when the supplier was
     * added) plus whatever remains unpaid across all purchases from them.
     */
    public function dueAmount(): float
    {
        $purchased = (float) $this->purchases()->sum('total');
        $paid = (float) $this->purchases()->sum('paid_amount');

        return round((float) $this->opening_balance + $purchased - $paid, 2);
    }
}
