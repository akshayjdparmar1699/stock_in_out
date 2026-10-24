<?php

use App\Models\Supplier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Suppliers had no branch concept at all before this — every supplier
     * was visible company-wide, to every branch. This backfills each
     * existing supplier to whichever branch(es) they've actually been
     * purchased from (their real usage history), so going forward each
     * branch only sees its own suppliers, matching how Customers already
     * work. A supplier with no purchase yet has no history to go on, so
     * it's attached to every branch in its company instead of silently
     * becoming invisible everywhere.
     */
    public function up(): void
    {
        Schema::create('branch_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'supplier_id']);
        });

        $now = now();

        $fromPurchases = DB::table('purchases')
            ->select('supplier_id', 'branch_id')
            ->distinct()
            ->get()
            ->map(fn ($row) => ['branch_id' => $row->branch_id, 'supplier_id' => $row->supplier_id, 'created_at' => $now, 'updated_at' => $now]);

        if ($fromPurchases->isNotEmpty()) {
            DB::table('branch_supplier')->insertOrIgnore($fromPurchases->all());
        }

        $suppliersWithoutPurchases = Supplier::whereDoesntHave('purchases')->get(['id', 'company_id']);

        $branchesByCompany = DB::table('branches')->pluck('id', 'company_id')->isEmpty()
            ? collect()
            : DB::table('branches')->get(['id', 'company_id'])->groupBy('company_id');

        $fallbackRows = $suppliersWithoutPurchases->flatMap(function (Supplier $supplier) use ($branchesByCompany, $now) {
            return ($branchesByCompany->get($supplier->company_id) ?? collect())
                ->map(fn ($branch) => [
                    'branch_id' => $branch->id,
                    'supplier_id' => $supplier->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        });

        if ($fallbackRows->isNotEmpty()) {
            DB::table('branch_supplier')->insertOrIgnore($fallbackRows->all());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_supplier');
    }
};
