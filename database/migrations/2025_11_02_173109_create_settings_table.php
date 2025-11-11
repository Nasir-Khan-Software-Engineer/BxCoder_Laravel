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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('name', 1000);
            $table->string('email', 200);
            $table->string('phone', 11);
            $table->string('address', 1000);

            $table->text('logo');
            $table->text('favicon');

            $table->string('meta_title', 200);
            $table->string('meta_keywords', 1000);
            $table->string('meta_description', 1000);

            $table->string('copyright', 200);

            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->string('tiktok')->nullable();

            $table->string('product_prefix', 10);
            $table->string('order_prefix', 10);
            $table->string('invoice_prefix', 10);

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
