<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'branch_id',
        'supplier_id',
        'user_id',
        'purchase_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class)->latest();
    }

    public function balanceDue(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    /**
     * A signed, no-login-required link to this purchase's PDF, so it can be
     * opened straight from a shared WhatsApp/other-app message.
     */
    public function sharedPdfUrl(): string
    {
        return URL::signedRoute('purchases.shared-pdf', ['purchase' => $this->id]);
    }

    public function shareMessage(): string
    {
        return <<<TEXT
        Purchase from {$this->supplier->name}
        Branch: {$this->branch->name}
        Purchase: {$this->purchase_number}
        Date: {$this->purchase_date->format('d M Y')}

        View / download (PDF):
        {$this->sharedPdfUrl()}

        Total: ₹{$this->formattedTotal()}
        Paid: ₹{$this->formattedPaid()}
        Balance Due: ₹{$this->formattedBalance()}
        TEXT;
    }

    private function formattedTotal(): string
    {
        return number_format((float) $this->total, 2);
    }

    private function formattedPaid(): string
    {
        return number_format((float) $this->paid_amount, 2);
    }

    private function formattedBalance(): string
    {
        return number_format($this->balanceDue(), 2);
    }
}
