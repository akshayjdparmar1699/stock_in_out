<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'staff_member_id',
        'user_id',
        'category',
        'amount',
        'expense_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function categories(): array
    {
        return [
            'salary' => 'Salary',
            'upad' => 'Advance',
            'petrol' => 'Petrol',
            'chai_pani' => 'Tea & Breakfast',
            'other' => 'Other',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categories()[$this->category] ?? 'Other';
    }
}
