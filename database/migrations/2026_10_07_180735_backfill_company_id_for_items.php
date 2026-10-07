<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every item that already existed before company_id did belongs to
     * the one real company that existed at the time (see
     * backfill_company_for_existing_data) — this just catches items up to
     * the same company its branches/users were already assigned to.
     */
    public function up(): void
    {
        $companyId = DB::table('companies')->orderBy('id')->value('id');

        if ($companyId) {
            DB::table('items')->whereNull('company_id')->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        DB::table('items')->update(['company_id' => null]);
    }
};
