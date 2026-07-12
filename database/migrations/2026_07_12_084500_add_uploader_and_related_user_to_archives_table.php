<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archives', function (Blueprint $table): void {
            $table->foreignId('uploaded_by')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('related_user_id')
                ->nullable()
                ->after('uploaded_by')
                ->constrained('users')
                ->nullOnDelete();
            $table->index('related_user_id');
            $table->index('uploaded_by');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::table('archives')->orderBy('id')->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('archives')->where('id', $row->id)->update([
                        'uploaded_by' => $row->user_id,
                        'related_user_id' => $row->user_id,
                    ]);
                }
            });
        } else {
            DB::statement('UPDATE archives SET uploaded_by = user_id, related_user_id = user_id');
        }
    }

    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('related_user_id');
            $table->dropConstrainedForeignId('uploaded_by');
        });
    }
};
