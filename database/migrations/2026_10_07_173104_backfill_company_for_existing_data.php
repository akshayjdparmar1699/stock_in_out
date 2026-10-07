<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every branch and user that already existed before the Company model
     * did belongs to this one real business — wraps them all under a
     * single Company row so nothing about how they use the app changes.
     * The name can be renamed later from the Companies screen.
     */
    public function up(): void
    {
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Om Sai Aallubhandar',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('branches')->whereNull('company_id')->update(['company_id' => $companyId]);
        DB::table('users')->whereNull('company_id')->update(['company_id' => $companyId]);
    }

    public function down(): void
    {
        $companyId = DB::table('companies')->where('name', 'Om Sai Aallubhandar')->value('id');

        if ($companyId) {
            DB::table('branches')->where('company_id', $companyId)->update(['company_id' => null]);
            DB::table('users')->where('company_id', $companyId)->update(['company_id' => null]);
            DB::table('companies')->whereKey($companyId)->delete();
        }
    }
};
