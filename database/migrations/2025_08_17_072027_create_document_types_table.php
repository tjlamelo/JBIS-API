<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->json('label');
            $table->string('storage_slug', 80);
            $table->boolean('unique_per_user')->default(false);
            $table->boolean('requires_expiry_date')->default(false);
            $table->boolean('requires_document_number')->default(false);
            $table->unsignedInteger('max_file_size_kb')->default(10240);
            $table->json('allowed_extensions');
            $table->json('allowed_mime_types');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('visible_to_candidates')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
