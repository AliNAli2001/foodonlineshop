<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_item_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->onDelete('cascade');

            $table->foreignId('inventory_batch_id')
                ->constrained('inventory_batches')
                ->onDelete('restrict');

            $table->unsignedInteger('quantity');

            $table->decimal('cost_price', 10, 2); // snapshot for accounting

            $table->timestamps();

            $table->unique(['order_item_id', 'inventory_batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_batches');
    }
};
