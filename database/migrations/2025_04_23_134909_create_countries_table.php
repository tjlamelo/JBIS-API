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
    Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->json('name'); // {"fr": "Cameroun", "en": "Cameroon"}
    $table->string('code', 3)->unique(); // CM, FR, BE
    $table->string('flag')->nullable(); // Emoji ou lien vers SVG
    $table->string('phone_code')->nullable(); // +237
    $table->boolean('is_active')->default(true)->index();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
