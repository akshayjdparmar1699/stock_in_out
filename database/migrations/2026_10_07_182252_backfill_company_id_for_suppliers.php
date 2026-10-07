<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companyId = DB::table('companies')->orderBy('id')->value('id');

        if ($companyId) {
            DB::table('suppliers')->whereNull('company_id')->update(['company_id' => $companyId]);
        }
    }

    public function down(): void
    {
        DB::table('suppliers')->update(['company_id' => null]);
    }
};
