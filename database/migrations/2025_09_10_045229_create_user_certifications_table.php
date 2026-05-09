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
        Schema::create('user_certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('certification_id')->constrained('certifications')->onDelete('cascade');

            $table->enum('status', ['OBTAINED', 'EXPIRED', 'REVOKED'])->default('OBTAINED');
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file')->nullable();
            $table->date('obtained_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_certifications');
    }
};
