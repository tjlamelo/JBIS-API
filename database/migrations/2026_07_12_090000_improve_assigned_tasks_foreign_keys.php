<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assigned_tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('assigned_tasks', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assigned_tasks', function (Blueprint $table): void {
            if (Schema::hasColumn('assigned_tasks', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
