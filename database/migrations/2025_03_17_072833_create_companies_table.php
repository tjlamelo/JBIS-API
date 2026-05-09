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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Infos principales
            $table->string('name');
            $table->string('slug')->unique(); // URLs : /companies/saar-assurance
            $table->string('industry')->nullable()->index();
            $table->string('country')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('address')->nullable();

            // Contact
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Description / infos supplémentaires
            $table->text('description')->nullable();
            $table->string('logo')->nullable();

            // Validation
            $table->boolean('is_approved')->default(false)->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
