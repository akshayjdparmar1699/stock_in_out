<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'quantity',
        'unit',
        'base_quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'base_quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * The unit this line was actually billed in — falls back to the
     * item's current unit for rows saved before per-line units existed.
     */
    public function displayUnit(): string
    {
        return $this->unit ?? $this->item->unit;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
