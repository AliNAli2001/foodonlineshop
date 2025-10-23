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
        // Update inventory_transactions table to add 'damaged' to transaction_type enum
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Drop the old enum column and recreate it with the new value
            $table->dropColumn('transaction_type');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['sale', 'restock', 'reservation', 'adjustment', 'damaged'])->after('reserved_change');
        });

        // Add inventory_transaction_id to damaged_goods table
        Schema::table('damaged_goods', function (Blueprint $table) {
            $table->foreignId('inventory_transaction_id')->nullable()->after('return_item_id')->constrained('inventory_transactions')->onDelete('set null');
            $table->index('inventory_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert inventory_transactions table
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['sale', 'restock', 'reservation', 'adjustment'])->after('reserved_change');
        });

        // Remove inventory_transaction_id from damaged_goods table
        Schema::table('damaged_goods', function (Blueprint $table) {
            $table->dropForeignIdFor('InventoryTransaction');
            $table->dropIndex(['inventory_transaction_id']);
            $table->dropColumn('inventory_transaction_id');
        });
    }
};

