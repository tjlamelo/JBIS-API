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
  Schema::create('interests_and_hobbies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Identité du hobby
    $table->string('title')->index(); // ex: "Production musicale", "Veille technologique"
    $table->string('category')->nullable()->index(); // ex: "Sport", "Art", "Tech"
 
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interests_and_hobbies');
    }
};
