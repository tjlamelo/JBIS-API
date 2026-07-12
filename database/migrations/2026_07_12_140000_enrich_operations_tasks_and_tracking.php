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
            $table->unsignedInteger('estimated_minutes')->nullable()->after('due_date');
            $table->unsignedInteger('minutes_spent')->default(0)->after('estimated_minutes');
            $table->date('week_start_date')->nullable()->after('minutes_spent')->index();
            $table->timestamp('started_at')->nullable()->after('final_result');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->foreignId('renewed_from_id')->nullable()->after('completed_at')->constrained('assigned_tasks')->nullOnDelete();
            $table->text('notes')->nullable()->after('renewed_from_id');
        });

        Schema::table('daily_tasks', function (Blueprint $table): void {
            $table->unsignedInteger('minutes_spent')->nullable()->after('hours_spent');
            $table->boolean('is_outside_meeting')->default(true)->after('assigned_task_id');
        });

        Schema::table('assigned_tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('assigned_tasks', 'meeting_id')) {
                return;
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table): void {
            $table->dropColumn(['minutes_spent', 'is_outside_meeting']);
        });

        Schema::table('assigned_tasks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('renewed_from_id');
            $table->dropColumn([
                'estimated_minutes',
                'minutes_spent',
                'week_start_date',
                'started_at',
                'completed_at',
                'notes',
            ]);
        });
    }
};
