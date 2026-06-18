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
        Schema::create('certification_offers', function (Blueprint $table) {
            $table->id();

            // Détails de la Certification
            $table->string('domain')->index(); // Ex: Réseaux, Cybersécurité, Langues (IELTS/TEF)
            $table->string('title');  // Ex: Cisco CCNA, AWS Cloud Practitioner
            $table->string('organization'); // Organisme (Cisco, Microsoft, British Council)
            $table->text('description')->nullable();

            // Financier et Logistique
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('currency', 10)->default('XAF');
            $table->enum('exam_mode', ['ONLINE', 'ONSITE', 'PROCTORED'])->default('ONSITE');

            // Validité et Reconnaissance
            $table->integer('validity_years')->nullable(); // Durée avant expiration
            $table->string('level')->nullable(); // Ex: Fondamental, Associé, Expert

            // Liaison avec le Workflow
            // Très important : Une certification peut avoir son propre Process Flow chez JBIS
            $table->foreignId('process_flow_id')->nullable()->constrained()->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_offers');
    }
};
