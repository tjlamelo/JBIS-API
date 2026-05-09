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
        if (! Schema::hasTable('language_program')) {
            Schema::create('language_program', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('language_id');
                $table->unsignedBigInteger('program_id');
                $table->boolean('is_mandatory')->default(true);
                $table->timestamps();
                $table->index('language_id');
                $table->index('program_id');
                $table->unique(['language_id', 'program_id']);
            });
        }

        if (! Schema::hasTable('language_offer')) {
            Schema::create('language_offer', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('language_id');
                $table->unsignedBigInteger('offer_id');
                $table->unsignedBigInteger('language_level_id')->nullable();
                $table->string('required_level')->nullable();
                $table->timestamps();
                $table->index('language_id');
                $table->index('offer_id');
                $table->index('language_level_id');
                $table->unique(['language_id', 'offer_id']);
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_offer');
        Schema::dropIfExists('language_program');
    }
};
