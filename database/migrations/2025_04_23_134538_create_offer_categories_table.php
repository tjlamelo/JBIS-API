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
        Schema::create('offer_categories', function (Blueprint $table) {
        $table->id();
        $table->json('name'); // Traduisible (Spatie)
        $table->string('slug')->unique();
        $table->string('icon')->nullable(); // Ex: "code", "heart", "building"
        $table->boolean('is_active')->default(true)->index();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_categories');
    }
};
