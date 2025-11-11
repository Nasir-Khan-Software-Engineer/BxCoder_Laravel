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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title', 1000)->unique();
            $table->string('slug', 1000)->unique();
            $table->string('keywords', 1000);
            $table->string('description', 1000);
            
            $table->text('image')->nullable();
            $table->longText('body');
            
            $table->string('project_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('code_url')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('is_active')->default(false);

            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
