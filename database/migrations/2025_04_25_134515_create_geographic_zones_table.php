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
     Schema::create('geographic_zones', function (Blueprint $table) {
        $table->id();
        $table->json('name'); // Traduisible (fr/en)
        $table->string('slug')->unique();
        $table->string('icon')->nullable(); // Nom de l'icône Lucide
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geographic_zones');
    }
};
