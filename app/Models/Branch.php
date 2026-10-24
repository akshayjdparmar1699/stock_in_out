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
        'gstin',
        'state',
        'state_code',
        'invoice_format',
        'invoice_accent',
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

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
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
     * A branch only prints a "Scan and pay" block on its bills once it has
     * every one of its own payment details set — no more falling back to
     * another branch's real bank account just because this one hasn't
     * configured its own yet.
     */
    public function hasPaymentDetails(): bool
    {
        return (bool) ($this->qr_code_path && $this->bank_name && $this->account_number
            && $this->account_holder_name && $this->ifsc_code);
    }

    public function paymentQrUrl(): ?string
    {
        return $this->qr_code_path ? Storage::disk('public')->url($this->qr_code_path) : null;
    }

    public function paymentQrPath(): ?string
    {
        return $this->qr_code_path ? Storage::disk('public')->path($this->qr_code_path) : null;
    }

    public function paymentQrMimeType(): string
    {
        return ($this->paymentQrPath() ? mime_content_type($this->paymentQrPath()) : null) ?: 'image/png';
    }

    public function paymentBankName(): ?string
    {
        return $this->bank_name;
    }

    public function paymentAccountNumber(): ?string
    {
        return $this->account_number;
    }

    public function paymentAccountHolderName(): ?string
    {
        return $this->account_holder_name;
    }

    public function paymentIfscCode(): ?string
    {
        return $this->ifsc_code;
    }
}
