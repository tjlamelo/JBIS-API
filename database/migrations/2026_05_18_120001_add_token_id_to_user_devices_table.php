<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_access_token_id')->nullable()->after('device_key');
            $table->index(['user_id', 'personal_access_token_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'personal_access_token_id']);
            $table->dropColumn('personal_access_token_id');
        });
    }
};
