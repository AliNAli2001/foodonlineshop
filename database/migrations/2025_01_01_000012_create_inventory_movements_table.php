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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->enum('transaction_type', ['sale', 'restock', 'reservation', 'adjustment', 'damaged', 'return']);
            $table->integer('available_change');
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('batch_number');
            $table->string('reference')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'created_at']);
            $table->index(['inventory_batch_id']);
            $table->index(['transaction_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
