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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->onDelete('set null');
            $table->integer('quantity_change');
            $table->integer('reserved_change')->default(0);
            $table->enum('transaction_type', ['sale', 'restock', 'reservation', 'adjustment', 'damaged']);
            $table->text('reason')->nullable();
            $table->decimal('cost_price', 10, 3);
            $table->timestamp('created_at')->useCurrent();
              // Add expiry_date column
            $table->date('expiry_date')->nullable();

            // Add batch number for tracking
            $table->string('batch_number')->nullable();
            $table->index('transaction_type');
            $table->index('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};

