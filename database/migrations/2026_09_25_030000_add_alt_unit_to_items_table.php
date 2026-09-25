<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // An item is always stocked/purchased in its base `unit` (e.g.
            // a bag), but some items are also sold to customers in a
            // different unit (e.g. loose kg out of that bag, or pieces).
            // alt_unit_ratio is how many alt_unit make up one base unit
            // (e.g. 1 bag = 50 kg -> alt_unit_ratio = 50), so a sale in
            // the alt unit can be converted back to base units for stock.
            $table->string('alt_unit')->nullable()->after('unit');
            $table->decimal('alt_unit_ratio', 12, 4)->nullable()->after('alt_unit');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['alt_unit', 'alt_unit_ratio']);
        });
    }
};
