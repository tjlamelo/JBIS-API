<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Préférences pays, historique visa et notes internes candidat (staff).
     */
    public function up(): void
    {
        Schema::create('user_preferred_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('priority')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'country_id']);
            $table->index('user_id');
        });

        Schema::create('user_visa_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries');
            $table->string('visa_type')->index();
            $table->string('visa_number', 50)->nullable();
            $table->enum('status', ['GRANTED', 'REFUSED', 'EXPIRED', 'CANCELLED'])->default('GRANTED')->index();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('rejection_date')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('user_documents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });

        Schema::create('user_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->text('content');
            $table->boolean('is_private')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notes');
        Schema::dropIfExists('user_visa_histories');
        Schema::dropIfExists('user_preferred_countries');
    }
};
