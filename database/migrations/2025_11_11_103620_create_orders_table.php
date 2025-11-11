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

            $table->string('invoice_no', 100)->unique();
            $table->dateTime('order_date');

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('total_price', 10, 2);
            $table->integer('total_items');
            $table->integer('total_qty');

            $table->string('status')->default('pending');
            $table->text('note')->nullable();

            $table->boolean('is_inside_dhaka')->default(false);
            $table->decimal('shipping_cost', 10, 2);

            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('pending');

            $table->text('address');

            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps(); // created_at and updated_at
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
