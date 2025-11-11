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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('minimum_alert_quantity')->default(5);
            $table->decimal('cost_price', 10, 3);
            $table->integer('version')->default(1);
            // Add expiry_date column
            $table->date('expiry_date')->nullable();

            // Add batch number for tracking
            $table->string('batch_number')->nullable();


            $table->timestamps();
            // Create composite index for product_id and expiry_date

            $table->index(['product_id', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
