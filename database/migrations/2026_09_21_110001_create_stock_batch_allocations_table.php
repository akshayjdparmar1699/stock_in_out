<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batch_allocations');
    }
};
