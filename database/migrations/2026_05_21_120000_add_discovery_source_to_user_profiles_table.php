<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->foreignId('discovery_source_id')
                ->nullable()
                ->after('user_id')
                ->constrained('discovery_sources')
                ->nullOnDelete();
            $table->string('discovery_source_other', 255)->nullable()->after('discovery_source_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('discovery_source_id');
            $table->dropColumn('discovery_source_other');
        });
    }
};
