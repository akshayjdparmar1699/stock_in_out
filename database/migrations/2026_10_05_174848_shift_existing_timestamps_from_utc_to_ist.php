<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every timestamp in this app so far was written using app.timezone =
     * UTC (e.g. "2026-10-05 12:16:42"), even though the shop runs on IST.
     * Switching app.timezone to Asia/Kolkata makes every NEW timestamp
     * correct, but it also changes how EXISTING naive timestamp columns
     * get interpreted on read — without this one-time correction, every
     * date already in the database would suddenly display 5 hours 30
     * minutes earlier than the real moment it happened. Shifting every
     * existing value forward by that same 5:30 here keeps them correct
     * under the new timezone, and since every row moves by the same
     * amount, nothing about their relative order (FIFO batches, invoice
     * numbering, running balances, etc.) changes.
     *
     * Deliberately left out: sessions, cache(_locks), jobs, job_batches,
     * failed_jobs, migrations, password_reset_tokens — transient/system
     * bookkeeping with no business meaning worth preserving.
     */
    private const SHIFT = "INTERVAL '5 hours 30 minutes'";

    private const TABLES = [
        'branches', 'customers', 'suppliers', 'staff_members',
        'items', 'item_stocks', 'invoices', 'invoice_items', 'invoice_payments',
        'purchases', 'purchase_items', 'purchase_payments',
        'stock_movements', 'stock_batches', 'stock_batch_allocations',
        'expenses', 'users', 'branch_customer', 'branch_staff_member',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            foreach (self::TABLES as $table) {
                DB::statement('UPDATE "'.$table.'" SET
                    created_at = created_at + '.self::SHIFT.',
                    updated_at = updated_at + '.self::SHIFT.'
                    WHERE created_at IS NOT NULL OR updated_at IS NOT NULL'
                );
            }

            DB::statement('UPDATE "users" SET email_verified_at = email_verified_at + '.self::SHIFT.' WHERE email_verified_at IS NOT NULL');
            DB::statement('UPDATE "stock_batches" SET received_at = received_at + '.self::SHIFT.' WHERE received_at IS NOT NULL');
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach (self::TABLES as $table) {
                DB::statement('UPDATE "'.$table.'" SET
                    created_at = created_at - '.self::SHIFT.',
                    updated_at = updated_at - '.self::SHIFT.'
                    WHERE created_at IS NOT NULL OR updated_at IS NOT NULL'
                );
            }

            DB::statement('UPDATE "users" SET email_verified_at = email_verified_at - '.self::SHIFT.' WHERE email_verified_at IS NOT NULL');
            DB::statement('UPDATE "stock_batches" SET received_at = received_at - '.self::SHIFT.' WHERE received_at IS NOT NULL');
        });
    }
};
