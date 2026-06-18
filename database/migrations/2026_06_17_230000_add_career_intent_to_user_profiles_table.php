<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_profiles', 'career_intent')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->string('career_intent', 32)->nullable()->after('residence_city')->index();
            });
        }

        if (! Schema::hasTable('user_settings')) {
            return;
        }

        $settings = DB::table('user_settings')
            ->select(['id', 'user_id', 'privacy'])
            ->whereNotNull('privacy')
            ->get();

        foreach ($settings as $row) {
            $privacy = json_decode((string) $row->privacy, true);
            if (! is_array($privacy)) {
                continue;
            }

            $careerIntent = $privacy['career_intent'] ?? null;
            if (! is_string($careerIntent) || $careerIntent === '') {
                continue;
            }

            DB::table('user_profiles')
                ->where('user_id', $row->user_id)
                ->update(['career_intent' => $careerIntent]);

            unset($privacy['career_intent']);
            DB::table('user_settings')
                ->where('id', $row->id)
                ->update(['privacy' => json_encode($privacy)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_profiles', 'career_intent')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('career_intent');
            });
        }
    }
};
