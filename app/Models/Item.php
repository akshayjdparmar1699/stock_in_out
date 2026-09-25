<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'alt_unit',
        'alt_unit_ratio',
        'description',
        'purchase_price',
        'selling_price',
        'low_stock_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'alt_unit_ratio' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function hasAltUnit(): bool
    {
        return (bool) $this->alt_unit && (float) $this->alt_unit_ratio > 0;
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ItemStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockForBranch(int $branchId): int|float
    {
        return $this->stocks->firstWhere('branch_id', $branchId)?->quantity ?? 0;
    }
}
