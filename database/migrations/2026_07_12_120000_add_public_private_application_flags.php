<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            $table->boolean('allows_public_applications')->default(true)->after('is_company_public');
        });

        Schema::table('applications', function (Blueprint $table): void {
            $table->boolean('is_private')->default(false)->after('status');
            $table->foreignId('created_by')->nullable()->after('is_private')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('is_private');
        });

        Schema::table('offers', function (Blueprint $table): void {
            $table->dropColumn('allows_public_applications');
        });
    }
};
