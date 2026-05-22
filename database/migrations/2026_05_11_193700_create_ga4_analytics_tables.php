<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ga4_daily_overview', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('active_users')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('engaged_sessions')->default(0);
            $table->decimal('engagement_rate', 7, 6)->default(0);
            $table->unsignedInteger('page_views')->default(0);
            $table->decimal('avg_session_duration', 10, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('ga4_daily_pages', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('page_path', 512);
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('active_users')->default(0);
            $table->timestamps();

            $table->unique(['date', 'page_path']);
            $table->index(['date']);
        });

        Schema::create('ga4_daily_acquisition', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('source', 128)->default('(direct)');
            $table->string('medium', 128)->default('(none)');
            $table->string('campaign', 256)->nullable();
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('active_users')->default(0);
            $table->timestamps();

            $table->unique(['date', 'source', 'medium', 'campaign'], 'ga4_acq_unique');
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ga4_daily_acquisition');
        Schema::dropIfExists('ga4_daily_pages');
        Schema::dropIfExists('ga4_daily_overview');
    }
};
