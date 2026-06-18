<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->json('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['category_id', 'slug'], 'trades_category_slug_unique');
            $table->index(['category_id', 'is_active'], 'trades_category_active_idx');
        });

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

        Schema::dropIfExists('trades');
    }
};
