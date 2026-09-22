<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('quantity_in', 12, 2);
            $table->decimal('quantity_remaining', 12, 2);
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['branch_id', 'item_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
