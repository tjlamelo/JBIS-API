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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_type_id')->nullable()->constrained('contract_types')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('user_documents')->nullOnDelete();
            // Détails du poste
            $table->string('job_title')->index();
            $table->string('company_name')->index();
            $table->string('company_website')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('offer_categories')->nullOnDelete();

            // Localisation
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('city_name')->nullable(); // Texte libre ou FK vers cities

            // Durée
            $table->date('start_date')->index();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);

            // Contenu (Translatable si besoin, mais souvent en une seule langue)
            $table->text('responsibilities')->nullable();
            $table->text('achievements')->nullable();

            // Validation
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
