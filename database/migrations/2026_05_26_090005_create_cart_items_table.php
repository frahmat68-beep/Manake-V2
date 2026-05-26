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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('price_per_day');
            $table->timestamps();

            $table->unique(
                ['user_id', 'equipment_id', 'rental_start_date', 'rental_end_date'],
                'cart_items_user_equip_dates_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
