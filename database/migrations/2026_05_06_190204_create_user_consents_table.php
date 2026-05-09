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
      Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Type de consentement : 'TERMS', 'PRIVACY', 'MARKETING'
            $table->string('type')->index(); 
            
            // Version du document (ex: 'v1.0' ou '2026-05-06')
            $table->string('version')->index(); 
            
            // Preuve de consentement
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('accepted_at')->useCurrent();
            
            // Index composé pour accélérer la vérification de conformité d'un utilisateur
            $table->index(['user_id', 'type', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_consents');
    }
};
