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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('discovery_source_id')
                ->nullable()
                ->constrained('discovery_sources')
                ->nullOnDelete();
            $table->string('discovery_source_other', 255)->nullable();

            $table->string('profile_origin', 16)->default('self');
            $table->unsignedBigInteger('recruiter_organization_id')->nullable();

            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 25)->nullable();
            $table->foreignId('nationality_country_id')->nullable()->constrained('countries');
            $table->string('residence_city', 120)->nullable();
            $table->string('career_intent', 32)->nullable()->index();
            $table->string('profile_type', 32)->nullable()->index();
            $table->foreignId('highest_education_level_id')
                ->nullable()
                ->constrained('education_levels')
                ->nullOnDelete();

            $table->string('address', 50)->nullable();
            $table->string('phone_number2', 20)->nullable()->unique();
            $table->string('phone_number3', 20)->nullable()->unique();
            $table->enum('gender', ['M', 'F'])->nullable();
            /** Photos d'identité (portrait, corps entier, etc.) — JSON, 3 entrées max. */
            $table->json('pictures')->nullable();
            $table->text('bio')->nullable();
            $table->enum('marital_status', ['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'])->nullable();
            $table->integer('number_of_children')->default(0);

            $table->string('email_institutional', 100)->nullable();
            $table->string('matricule', 50)->nullable();
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('cascade');

            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
