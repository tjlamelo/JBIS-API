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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();

            // --- IDENTITÉ & SEO ---
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('slug'); // JSON pour multilingue
            $table->json('image_media')->nullable();

            // --- RELATIONS ---
            $table->foreignId('geographic_zone_id')
                ->nullable()
                ->constrained('geographic_zones')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // --- FINANCE & TEMPS ---

            $table->integer('procedure_duration')->nullable();
            $table->string('duration_unit', 20)->default('months');

            // --- CRITÈRES (Considérer une table pivot si multi-critères) ---
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);

            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED', 'EXPIRED'])->default('PUBLISHED')->index();

            // --- TIMESTAMPS & DATES ---
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // --- COLONNES GÉNÉRÉES POUR MARIA DB / MYSQL ---
            $table->text('name_fr')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(name, '$.fr'))");
            $table->text('description_fr')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(description, '$.fr'))");
            $table->text('name_en')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))");
            $table->text('description_en')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))");

            // --- INDEX FULLTEXT (Recherche globale JBIS) ---
            $table->fullText(['name_fr', 'description_fr'], 'programs_fr_fulltext');
            $table->fullText(['name_en', 'description_en'], 'programs_en_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
