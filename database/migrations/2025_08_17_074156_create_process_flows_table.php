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
      Schema::create('process_flows', function (Blueprint $table) {
    $table->id();
    $table->uuid('flow_group_id')->index(); // ID commun pour toutes les versions d'un flow
    $table->integer('version')->default(1);
    $table->boolean('is_current')->default(true);
    
    $table->string('name'); // ex: "Programme Travail Canada"
    $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
    $table->unique(['flow_group_id', 'version']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_flows');
    }
};
