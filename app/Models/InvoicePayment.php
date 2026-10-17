<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory;

    public const MODE_CASH = 'cash';

    public const MODE_UPI = 'upi';

    public const MODE_BANK = 'bank';

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'user_id',
        'amount',
        'type',
        'note',
        'mode',
        'batch_id',
        'from_credit_balance',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'from_credit_balance' => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
