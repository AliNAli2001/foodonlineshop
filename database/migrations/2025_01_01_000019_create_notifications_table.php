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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->enum('recipient_type', ['client', 'delivery']);
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->enum('message_type', ['whatsapp', 'email']);
            $table->text('message_content');
            $table->foreignId('template_id')->nullable()->constrained('messages_template')->onDelete('set null');
            $table->timestamp('sent_at')->useCurrent();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->index(['recipient_id', 'recipient_type']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

