<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Branch extends Model
{
    use HasFactory;

    protected const DEFAULT_BANK_NAME = 'HDFC Bank';

    protected const DEFAULT_ACCOUNT_NUMBER = '50200097420397';

    protected const DEFAULT_ACCOUNT_HOLDER_NAME = 'Om Sai Aalubhandar';

    protected const DEFAULT_IFSC_CODE = 'HDFC0004196';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'is_active',
        'qr_code_path',
        'bank_name',
        'account_number',
        'account_holder_name',
        'ifsc_code',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function itemStocks(): HasMany
    {
        return $this->hasMany(ItemStock::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function staffMembers(): BelongsToMany
    {
        return $this->belongsToMany(StaffMember::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Falls back to the original single-branch defaults so a branch with no
     * payment details of its own keeps showing what it always showed,
     * instead of a blank QR/bank block.
     */
    public function paymentQrUrl(): string
    {
        return $this->qr_code_path
            ? Storage::disk('public')->url($this->qr_code_path)
            : asset('images/payment-qr.png');
    }

    public function paymentQrPath(): string
    {
        return $this->qr_code_path
            ? Storage::disk('public')->path($this->qr_code_path)
            : public_path('images/payment-qr.png');
    }

    public function paymentQrMimeType(): string
    {
        return mime_content_type($this->paymentQrPath()) ?: 'image/png';
    }

    public function paymentBankName(): string
    {
        return $this->bank_name ?: self::DEFAULT_BANK_NAME;
    }

    public function paymentAccountNumber(): string
    {
        return $this->account_number ?: self::DEFAULT_ACCOUNT_NUMBER;
    }

    public function paymentAccountHolderName(): string
    {
        return $this->account_holder_name ?: self::DEFAULT_ACCOUNT_HOLDER_NAME;
    }

    public function paymentIfscCode(): string
    {
        return $this->ifsc_code ?: self::DEFAULT_IFSC_CODE;
    }
}
