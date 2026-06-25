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
            $table->string('order_code')->unique();
            $table->string('customer_name');
            $table->string('whatsapp_normalized');
            $table->string('whatsapp_display');
            $table->string('payment_status');
            $table->string('order_status');
            $table->string('fulfillment_type');
            $table->foreignId('schedule_slot_id')->nullable()->constrained('schedule_slots')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('grand_total');
            $table->text('customer_note')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->dateTime('payment_expires_at');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Indexes for lookup and performance
            $table->index(['whatsapp_normalized', 'order_code']);
            $table->index('order_status');
            $table->index('payment_status');
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
