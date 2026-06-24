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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('whatsapp', 20);
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->unsignedBigInteger('base_delivery_fee')->default(0);
            $table->unsignedBigInteger('delivery_fee_per_km')->default(0);
            $table->unsignedInteger('max_delivery_distance_meters')->default(10000);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
