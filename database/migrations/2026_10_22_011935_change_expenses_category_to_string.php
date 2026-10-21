<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The expense category list keeps growing (Transport, Labour, Fuel,
     * Rent, Electricity, Phone, Donation...), so the original MySQL ENUM /
     * Postgres CHECK-constraint column is swapped for a plain string — the
     * allowed values are already enforced in the app layer (Expense::
     * categories(), StoreExpenseRequest) instead.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_category_check');
            DB::statement('ALTER TABLE expenses ALTER COLUMN category TYPE VARCHAR(255)');
        } else {
            DB::statement('ALTER TABLE expenses MODIFY category VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        // Intentionally not reversible — the previous ENUM/CHECK list no
        // longer matches the categories already in use.
    }
};
