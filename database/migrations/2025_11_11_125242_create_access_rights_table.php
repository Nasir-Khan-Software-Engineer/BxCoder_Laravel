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
        Schema::create('access_rights', function (Blueprint $table) {
            $table->id();

            $table->string('route_name', 200);
            $table->string('short_id', 200);
            $table->string('short_description', 300);
            $table->string('details', 1000)->nullable();

            $table->timestamps();
        });


        Schema::create('access_right_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('access_right_id')->constrained('access_rights')->onDelete('cascade');
            $table->unique(['role_id', 'access_right_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_rights');
        Schema::dropIfExists('access_right_role');
    }
};
