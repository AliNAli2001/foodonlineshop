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
            $table->decimal('dollar_exchange_rate', 10, 4)->default(1.0000);
            $table->integer('general_minimum_alert_quantity')->default(10);
            $table->integer('max_order_items')->default(50);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            'dollar_exchange_rate' => 3.75,
            'general_minimum_alert_quantity' => 10,
            'max_order_items' => 50,
            'created_at' => now(),
            'updated_at' => now(),
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

