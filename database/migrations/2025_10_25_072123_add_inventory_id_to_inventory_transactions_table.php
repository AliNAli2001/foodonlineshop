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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('inventory_id')->nullable()->after('product_id')->constrained('inventory')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['inventory_id']);
            // Drop the inventory_id column
            $table->dropColumn('inventory_id');

        });
    }
};
