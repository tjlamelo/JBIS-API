<?php
namespace Database\Factories;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $proficiencyLevels = [
            'elementary_proficiency',
            'limited_working_proficiency',
            'professional_working_proficiency',
            'full_professional_proficiency',
            'native_or_bilingual_proficiency',
        ];

        $languageId = DB::table('languages')->inRandomOrder()->value('id');

        return [
            'user_id' => User::factory(),
            'language_id' => $languageId ?? 1,
            'proficiency_level' => $this->faker->randomElement($proficiencyLevels),
        ];
    }
}
