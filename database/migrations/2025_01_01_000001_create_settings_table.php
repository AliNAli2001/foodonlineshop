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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('dollar_exchange_rate', 12, 2)->default(1.00);
            $table->integer('general_minimum_alert_quantity')->default(10);
            $table->integer('max_order_items')->default(50);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Insert default settings
        DB::table('settings')->insert([
            'id' => 1,
            'dollar_exchange_rate' => 1.0000,
            'general_minimum_alert_quantity' => 10,
            'max_order_items' => 50,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

