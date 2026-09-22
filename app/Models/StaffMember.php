<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'monthly_salary',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isPartner(): bool
    {
        return $this->type === 'partner';
    }

    public function totalUpad(): float
    {
        return (float) $this->expenses()->where('category', 'upad')->sum('amount');
    }

    public function totalSalaryPaid(): float
    {
        return (float) $this->expenses()->where('category', 'salary')->sum('amount');
    }
}
