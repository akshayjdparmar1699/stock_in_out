<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'item_id',
        'user_id',
        'type',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The batch/lot this movement created, when it is a stock-in.
     */
    public function batch(): HasOne
    {
        return $this->hasOne(StockBatch::class);
    }

    /**
     * The batches this movement drew from, when it is a stock-out.
     */
    public function batchAllocations(): HasMany
    {
        return $this->hasMany(StockBatchAllocation::class);
    }

    /**
     * Link to the invoice/purchase that caused this movement, if any.
     */
    public function referenceUrl(): ?string
    {
        return match ($this->reference_type) {
            Invoice::class => route('invoices.show', $this->reference_id),
            Purchase::class => route('purchases.show', $this->reference_id),
            default => null,
        };
    }

    public function referenceLabel(): ?string
    {
        return match ($this->reference_type) {
            Invoice::class => 'View invoice',
            Purchase::class => 'View purchase',
            default => null,
        };
    }
}
