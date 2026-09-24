<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'branch_id',
        'customer_id',
        'user_id',
        'invoice_date',
        'subtotal',
        'discount',
        'tax',
        'transportation',
        'total',
        'paid_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'transportation' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->latest();
    }

    public function balanceDue(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    public function whatsappMessage(): string
    {
        return <<<TEXT
        Hello {$this->customer->name},

        Your bill from {$this->branch->name} is ready:
        Invoice: {$this->invoice_number}
        Date: {$this->invoice_date->format('d M Y')}

        Total: ₹{$this->formattedTotal()}
        Paid: ₹{$this->formattedPaid()}
        Balance Due: ₹{$this->formattedBalance()}

        Thank you for your business!
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
