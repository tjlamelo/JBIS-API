<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permission_overrides', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('permission_name');
            $table->enum('effect', ['allow', 'deny']);
            $table->timestamps();

            $table->unique(['user_id', 'permission_name'], 'user_permission_overrides_user_permission_unique');
            $table->index(['permission_name', 'effect'], 'user_permission_overrides_permission_effect_index');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permission_overrides');
    }
};
