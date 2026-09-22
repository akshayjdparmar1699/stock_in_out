<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'item_id',
        'stock_movement_id',
        'unit_cost',
        'quantity_in',
        'quantity_remaining',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'quantity_in' => 'decimal:2',
            'quantity_remaining' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function sourceMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(StockBatchAllocation::class);
    }

    public function quantitySold(): float
    {
        return (float) $this->quantity_in - (float) $this->quantity_remaining;
    }

    public function isDepleted(): bool
    {
        return (float) $this->quantity_remaining <= 0;
    }
}
