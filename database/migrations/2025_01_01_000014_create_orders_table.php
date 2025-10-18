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
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('restrict');
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->onDelete('restrict');
            $table->timestamp('order_date')->useCurrent();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'canceled', 'returned'])->default('pending');
            $table->enum('order_source', ['inside_city', 'outside_city']);
            $table->enum('delivery_method', ['delivery', 'shipping', 'hand_delivered']);
            $table->text('shipping_notes')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->text('address_details')->nullable();
            $table->text('general_notes')->nullable();
            $table->foreignId('delivery_id')->nullable()->constrained('delivery')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index('client_id');
            $table->index('created_by_admin_id');
            $table->index('delivery_id');
            $table->index('status');
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

