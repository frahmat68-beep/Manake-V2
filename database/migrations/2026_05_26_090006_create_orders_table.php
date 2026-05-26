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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->unsignedInteger('duration_days');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(11.00);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('additional_fee')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('payment_status')->default('pending');
            $table->string('rental_status')->default('waiting_payment');
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->unsignedInteger('reschedule_count')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('payment_status');
            $table->index('rental_status');
            $table->index(['rental_start_date', 'rental_end_date'], 'orders_rental_dates_idx');
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
