<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
   // Migration pour les étapes types
Schema::create('process_steps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('process_flow_id')->constrained()->cascadeOnDelete();
    
    $table->integer('step_order')->default(1);
    $table->string('title');
    $table->text('description')->nullable();
    
    // Paramètres par défaut copiés lors de la candidature
    $table->decimal('default_amount', 15, 2)->default(0);
    $table->boolean('requires_documents')->default(false);
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
