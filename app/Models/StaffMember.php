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

    /**
     * Sums expenses for a given category within a calendar month, matching
     * on `for_month` when set, falling back to `expense_date` for older
     * rows recorded before that column existed.
     */
    private function sumForMonth(string $category, \Illuminate\Support\Carbon $month): float
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        return (float) $this->expenses()
            ->where('category', $category)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('for_month', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereNull('for_month')->whereBetween('expense_date', [$start, $end]);
                    });
            })
            ->sum('amount');
    }

    public function advanceForMonth(\Illuminate\Support\Carbon $month): float
    {
        return $this->sumForMonth('upad', $month);
    }

    public function salaryPaidForMonth(\Illuminate\Support\Carbon $month): float
    {
        return $this->sumForMonth('salary', $month);
    }

    public function leftToPayForMonth(\Illuminate\Support\Carbon $month): float
    {
        return max(0, (float) $this->monthly_salary - $this->advanceForMonth($month) - $this->salaryPaidForMonth($month));
    }
}
