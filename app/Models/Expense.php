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
        'mode',
        'for_month',
        'expense_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'for_month' => 'date',
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
            'transport' => 'Transport',
            'labour' => 'Labour',
            'fuel' => 'Fuel',
            'salary' => 'Salary',
            'upad' => 'Advance',
            'rent' => 'Rent',
            'electricity' => 'Electricity',
            'phone' => 'Phone',
            'donation' => 'Donation',
            'other' => 'Other',
            // Legacy keys kept so old rows still render a label/icon correctly.
            'petrol' => 'Petrol',
            'chai_pani' => 'Tea & Breakfast',
        ];
    }

    /**
     * The categories shown as pickable buttons on the Add Expense page —
     * legacy aliases ('petrol', 'chai_pani') are excluded since 'fuel' and
     * 'other' already cover them going forward.
     */
    public static function primaryCategories(): array
    {
        return array_slice(self::categories(), 0, 10, true);
    }

    public static function categoryMeta(): array
    {
        return [
            'transport' => ['icon' => 'truck', 'color' => 'bg-blue-100 text-blue-600'],
            'labour' => ['icon' => 'briefcase', 'color' => 'bg-amber-100 text-amber-700'],
            'fuel' => ['icon' => 'fire', 'color' => 'bg-red-100 text-red-600'],
            'petrol' => ['icon' => 'fire', 'color' => 'bg-red-100 text-red-600'],
            'salary' => ['icon' => 'currency', 'color' => 'bg-purple-100 text-purple-700'],
            'upad' => ['icon' => 'arrow-right', 'color' => 'bg-violet-100 text-violet-700'],
            'rent' => ['icon' => 'home', 'color' => 'bg-green-100 text-green-700'],
            'electricity' => ['icon' => 'bolt', 'color' => 'bg-yellow-100 text-yellow-700'],
            'phone' => ['icon' => 'phone', 'color' => 'bg-teal-100 text-teal-700'],
            'donation' => ['icon' => 'heart', 'color' => 'bg-pink-100 text-pink-600'],
            'chai_pani' => ['icon' => 'other', 'color' => 'bg-gray-100 text-gray-600'],
            'other' => ['icon' => 'other', 'color' => 'bg-gray-100 text-gray-600'],
        ];
    }

    public function categoryLabel(): string
    {
        return self::categories()[$this->category] ?? 'Other';
    }

    public function categoryIcon(): string
    {
        return self::categoryMeta()[$this->category]['icon'] ?? 'other';
    }

    public function categoryColor(): string
    {
        return self::categoryMeta()[$this->category]['color'] ?? 'bg-gray-100 text-gray-600';
    }
}
