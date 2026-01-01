<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate product_stocks table from existing inventory_batches
        DB::statement("
            INSERT INTO product_stocks (product_id, available_quantity, reserved_quantity, created_at, updated_at)
            SELECT 
                product_id,
                COALESCE(SUM(available_quantity), 0) as available_quantity,
                COALESCE(SUM(reserved_quantity), 0) as reserved_quantity,
                NOW() as created_at,
                NOW() as updated_at
            FROM inventory_batches
            WHERE status != 'expired'
              AND (expiry_date IS NULL OR expiry_date >= CURDATE())
            GROUP BY product_id
            ON DUPLICATE KEY UPDATE
                available_quantity = VALUES(available_quantity),
                reserved_quantity = VALUES(reserved_quantity),
                updated_at = NOW()
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the product_stocks table
        DB::table('product_stocks')->truncate();
    }
};

