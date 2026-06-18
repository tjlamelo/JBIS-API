<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_profiles', 'trade_id')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('trade_id');
            });
        }

        if (Schema::hasColumn('user_profiles', 'total_years_of_experience')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('total_years_of_experience');
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'total_years_of_experience')) {
                $table->unsignedInteger('total_years_of_experience')->default(0)->index();
            }
            if (! Schema::hasColumn('user_profiles', 'trade_id')) {
                $table->foreignId('trade_id')->nullable()->after('residence_city')->constrained('trades')->nullOnDelete();
            }
        });
    }
};
