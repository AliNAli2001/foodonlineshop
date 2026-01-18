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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('available_quantity')->default(0);
            $table->decimal('cost_price', 10, 2);
            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['active', 'expired', 'depleted'])->default('active');
            $table->timestamps();
            $table->unique(['product_id', 'batch_number', 'expiry_date']);
            $table->index(['product_id', 'expiry_date', 'status']);
            $table->index('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};