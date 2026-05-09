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
        Schema::create('assigned_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT'])->default('MEDIUM');

            // État d'avancement global
            $table->integer('progress_percentage')->default(0); // 0 à 100
            $table->enum('status', ['TODO', 'IN_PROGRESS', 'DONE', 'CANCELLED'])->default('TODO');
            $table->text('final_result')->nullable(); // Résultat final une fois terminé

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_tasks');
    }
};
