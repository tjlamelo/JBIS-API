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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // --- RELATIONS ---
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->constrained('contract_types')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Ces tables sont créées dans des migrations plus récentes.
            // On déclare d'abord les colonnes, puis on ajoute les FK plus tard.
            $table->foreignId('offer_type_id')->nullable();
            $table->foreignId('work_schedule_id')->nullable();
            $table->foreignId('education_level_id')->nullable();

            // --- JSON (Spatie Translatable) ---
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('slug');

            $table->json('photo_media')->nullable();

            $table->json('responsibilities')->nullable();
            $table->json('requirements')->nullable();

            // --- LOCALISATION ---
            $table->string('address')->nullable();

            // --- SALAIRES ---
            $table->decimal('salary_min', 15, 2)->unsigned()->nullable();
            $table->decimal('salary_max', 15, 2)->unsigned()->nullable();
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_salary_public')->default(false);
            $table->boolean('is_company_public')->default(false);

            // --- CONDITIONS ---
            $table->enum('work_mode', ['on-site', 'hybrid', 'remote'])->default('on-site')->index();
            $table->unsignedInteger('available_positions')->default(1);
            $table->json('meta')->nullable();

            // --- STATUT & VISIBILITÉ ---
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED', 'EXPIRED'])->default('PUBLISHED')->index();
            $table->unsignedBigInteger('views_count')->default(0);

            // --- DATES ---
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expiration_date')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // --- INDEX COMPOSITES ---
            $table->index(['status', 'published_at', 'expiration_date'], 'offers_listing_index');
            $table->index(['company_id', 'status'], 'offers_company_status');
            $table->index(['city_id', 'status'], 'offers_city_status');
            $table->index(['offer_type_id', 'status'], 'offers_type_status');
            $table->index(['category_id', 'status'], 'offers_category_status');
            $table->index(['is_featured', 'is_urgent', 'status'], 'offers_boost_index');
            $table->index(['salary_min', 'salary_max'], 'offers_salary_range');

            // --- COLONNES GÉNÉRÉES (FullText) ---
            $table->string('slug_fr')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.fr'))")->unique();
            $table->string('slug_en')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.en'))")->nullable();
            $table->text('title_fr')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.fr'))");
            $table->text('title_en')->storedAs("JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))");
            $table->text('description_fr')->storedAs("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(description, '$.fr')), '')");
            $table->text('description_en')->storedAs("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en')), '')");

            // --- INDEX FULLTEXT ---
            $table->fullText(['title_fr', 'description_fr'], 'offers_fr_fulltext');
            $table->fullText(['title_en', 'description_en'], 'offers_en_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
