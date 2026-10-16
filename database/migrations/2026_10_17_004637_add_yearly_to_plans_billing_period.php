<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Silver and Gold moved from monthly to yearly billing, so the
     * billing_period enum needs a third value. doctrine/dbal isn't
     * installed (no Schema::table()->change() available), and Postgres
     * implements Laravel's enum() as a CHECK constraint rather than a
     * native MySQL-style enum, so each driver needs its own statement.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE plans DROP CONSTRAINT plans_billing_period_check');
            DB::statement("ALTER TABLE plans ADD CONSTRAINT plans_billing_period_check CHECK (billing_period IN ('trial', 'monthly', 'yearly'))");
        } else {
            DB::statement("ALTER TABLE plans MODIFY billing_period ENUM('trial', 'monthly', 'yearly') NOT NULL DEFAULT 'monthly'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE plans DROP CONSTRAINT plans_billing_period_check');
            DB::statement("ALTER TABLE plans ADD CONSTRAINT plans_billing_period_check CHECK (billing_period IN ('trial', 'monthly'))");
        } else {
            DB::statement("ALTER TABLE plans MODIFY billing_period ENUM('trial', 'monthly') NOT NULL DEFAULT 'monthly'");
        }
    }
};
