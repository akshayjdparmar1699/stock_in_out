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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    /**
     * Total outstanding balance: opening due (set when the customer was
     * created) plus whatever remains unpaid across all their invoices.
     * A negative value means the customer is in credit.
     */
    public function dueAmount(): float
    {
        $invoiced = (float) $this->invoices()->sum('total');
        $paid = (float) $this->invoices()->sum('paid_amount');

        return round((float) $this->opening_balance + $invoiced - $paid, 2);
    }

    public function whatsappNumber(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->phone);

        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return $digits ?: null;
    }

    public function whatsappUrl(string $message = ''): ?string
    {
        $number = $this->whatsappNumber();

        if (! $number) {
            return null;
        }

        $url = "https://wa.me/{$number}";

        if ($message !== '') {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }
}
