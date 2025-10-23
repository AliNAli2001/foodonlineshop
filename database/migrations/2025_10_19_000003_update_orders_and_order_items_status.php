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
        // Update orders table: remove 'returned' from status enum and add 'done'
        Schema::table('orders', function (Blueprint $table) {
            // Drop the old enum and create a new one
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'done', 'canceled'])->default('pending')->after('total_amount');
        });

        // Update order_items table: add status column
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('status', ['normal', 'returned'])->default('normal')->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'canceled', 'returned'])->default('pending')->after('total_amount');
        });

        // Revert order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

