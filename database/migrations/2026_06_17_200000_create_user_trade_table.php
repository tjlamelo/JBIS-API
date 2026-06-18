<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_trade')) {
            return;
        }

        Schema::create('user_trade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_id')->constrained('trades')->cascadeOnDelete();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'trade_id'], 'user_trade_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_trade');
    }
};
