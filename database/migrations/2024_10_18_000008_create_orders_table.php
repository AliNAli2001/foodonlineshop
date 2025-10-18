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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('assigned_delivery_id')->nullable()->constrained('delivery_personnel')->onDelete('set null');
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'in_delivery', 'delivered', 'cancelled'])->default('pending');
            $table->enum('order_source', ['inside_city', 'outside_city'])->default('inside_city');
            $table->enum('delivery_method', ['delivery', 'pickup'])->default('delivery');
            $table->decimal('total_amount_sar', 10, 2);
            $table->decimal('total_amount_usd', 10, 2)->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

