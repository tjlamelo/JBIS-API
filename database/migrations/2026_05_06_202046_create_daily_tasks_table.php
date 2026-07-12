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
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // LIEN OPTIONNEL : Si la tâche du jour découle d'une réunion
            $table->foreignId('assigned_task_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_outside_meeting')->default(true);

            $table->string('title'); // Ce que la personne a fait concrètement aujourd'hui
            $table->text('description')->nullable();
            $table->date('task_date');

            $table->integer('hours_spent')->nullable();
            $table->unsignedInteger('minutes_spent')->nullable();
            $table->enum('status', ['COMPLETED', 'PARTIAL', 'BLOCKED'])->default('COMPLETED');
            $table->text('blockers_notes')->nullable(); // Si bloqué, pourquoi ?

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_tasks');
    }
};
