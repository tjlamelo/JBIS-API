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
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_level_id')->nullable()->constrained('education_levels')->nullOnDelete();

            // Relation vers le document spécifique (le scan du diplôme)
            $table->foreignId('document_id')->nullable()->constrained('user_documents')->nullOnDelete();

            $table->string('degree');
            $table->string('institution_name')->index();
            $table->string('field_of_study')->nullable();

            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('city_name')->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
