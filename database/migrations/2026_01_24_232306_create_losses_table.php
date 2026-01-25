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
        Schema::create('losses', function (Blueprint $table) {
            $table->id();

            $table->integer('quantity')->nullable(); // الكمية، يمكن أن تكون null إذا لم تكن ذات صلة ببعض الأنواع

            $table->enum('type', ['shipping_costs', 'general_costs', 'delivery_costs', 'other'])->default('other'); // النوع، مع خيارات محددة وخيار 'other' للإضافات

            $table->text('reason'); // السبب، نص طويل

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
