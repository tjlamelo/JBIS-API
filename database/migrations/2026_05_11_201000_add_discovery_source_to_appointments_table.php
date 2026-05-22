<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->foreignId('discovery_source_id')
                ->nullable()
                ->after('message')
                ->constrained('discovery_sources');

            $table->string('discovery_source_other', 255)->nullable()->after('discovery_source_id');

            $table->string('utm_source', 128)->nullable()->after('discovery_source_other');
            $table->string('utm_medium', 128)->nullable()->after('utm_source');
            $table->string('utm_campaign', 128)->nullable()->after('utm_medium');

            $table->ipAddress('ip_address')->nullable()->after('utm_campaign');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('discovery_source_id');
            $table->dropColumn([
                'discovery_source_other',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
