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
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();

            $table->decimal('quantity', 14, 2)->required();

            $table->enum('adjustment_type', ['gain', 'loss'])->default('loss'); // نوع التعديل: ربح أو خسارة

            $table->text('reason'); // السبب، نص طويل
            $table->date('date')->nullable()->default(now());
            $table->morphs("adjustable"); // العلاقة مع الكائنات الأخرى
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
