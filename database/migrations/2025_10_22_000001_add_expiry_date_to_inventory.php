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
        // Modify inventory table to support multiple rows per product with expiry dates
        Schema::table('inventory', function (Blueprint $table) {
            // Remove unique constraint on product_id to allow multiple rows per product
            $table->dropUnique(['product_id']);

            // Add expiry_date column
            $table->date('expiry_date')->nullable();

            // Add batch number for tracking
            $table->string('batch_number')->nullable();

            // Create composite index for product_id and expiry_date
            $table->index(['product_id', 'expiry_date']);
        });

        // Add expiry_date to inventory_transactions table
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'batch_number']);
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'expiry_date']);
            $table->dropColumn(['expiry_date', 'batch_number']);
            $table->unique('product_id');
        });
    }
};

