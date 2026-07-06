<?php

declare(strict_types=1);

namespace Tests\Unit\Identity\Actions\Document;

use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Skill;
use App\Core\Domain\Catalog\Models\SkillCategory;
use App\Core\Domain\Identity\Actions\Document\ApplyUserDocumentExtractionAction;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\Education;
use App\Core\Domain\Identity\Models\Experience;
use App\Core\Domain\Identity\Models\InterestAndHobby;
use App\Core\Domain\Identity\Models\Language as UserLanguage;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Models\UserDocumentExtraction;
use App\Core\Domain\Identity\Models\UserSkill;
use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Location\Models\LanguageLevel;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use Database\Seeders\LanguageLevelSeeder;
use Database\Seeders\LanguageSeeder;
use Tests\TestCase;

final class ApplyUserDocumentExtractionActionTest extends TestCase
{
    public function test_persists_interests_on_apply(): void
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();
        $type = DocumentType::query()->where('code', 'CV')->first()
            ?? DocumentType::factory()->create(['code' => 'CV', 'label' => ['fr' => 'CV', 'en' => 'CV']]);

        $document = UserDocument::factory()->create([
            'user_id' => $user->id,
            'document_type_id' => $type->id,
        ]);

        $extraction = UserDocumentExtraction::query()->create([
            'user_document_id' => $document->id,
            'user_id' => $user->id,
            'document_type_code' => 'CV',
            'status' => DocumentExtractionStatus::PendingReview,
            'draft_payload' => [
                'user_profile' => ['first_name' => 'Jean'],
                'interests' => [
                    ['name' => 'Football'],
                    ['name' => 'Lecture'],
                ],
            ],
        ]);

        $this->app->make(ApplyUserDocumentExtractionAction::class)->execute($extraction, $reviewer);

        $titles = InterestAndHobby::query()
            ->where('user_id', $user->id)
            ->orderBy('title')
            ->pluck('title')
            ->all();

        self::assertSame(['Football', 'Lecture'], $titles);
    }

    public function test_skips_duplicate_sections_and_persists_matched_skills(): void
    {
        $this->seed([LanguageSeeder::class, LanguageLevelSeeder::class]);

        $user = User::factory()->create();
        $reviewer = User::factory()->create();
        $type = DocumentType::query()->where('code', 'CV')->first()
            ?? DocumentType::factory()->create(['code' => 'CV', 'label' => ['fr' => 'CV', 'en' => 'CV']]);

        $document = UserDocument::factory()->create([
            'user_id' => $user->id,
            'document_type_id' => $type->id,
        ]);

        Education::query()->create([
            'user_id' => $user->id,
            'degree' => 'Licence soins infirmiers',
            'institution_name' => 'Ecole de Bafoussam',
            'start_date' => '2003-07-01',
            'end_date' => '2013-07-01',
            'is_current' => false,
        ]);

        $frenchId = (int) Language::query()->where('code', 'fr')->value('id');
        $englishId = (int) Language::query()->where('code', 'en')->value('id');
        $intermediateLevelId = (int) LanguageLevel::query()->where('code', 'limited_working_proficiency')->value('id');
        $beginnerLevelId = (int) LanguageLevel::query()->where('code', 'elementary_proficiency')->value('id');

        UserLanguage::query()->create([
            'user_id' => $user->id,
            'language_id' => $frenchId,
            'language_level_id' => $intermediateLevelId,
            'is_approved' => false,
        ]);
        UserLanguage::query()->create([
            'user_id' => $user->id,
            'language_id' => $frenchId,
            'language_level_id' => $beginnerLevelId,
            'is_approved' => false,
        ]);

        $skillCategory = SkillCategory::query()->updateOrCreate(
            ['slug' => 'soft-test-'.uniqid()],
            ['name' => ['fr' => 'Soft', 'en' => 'Soft']],
        );
        $category = Category::query()->first() ?? Category::factory()->create();
        $skill = Skill::query()->updateOrCreate(
            ['slug' => 'organisation-planification-test-'.uniqid()],
            [
                'name' => ['fr' => 'Organisation et planification', 'en' => 'Organization and planning'],
                'skill_category_id' => $skillCategory->id,
                'category_id' => $category->id,
            ],
        );

        $extraction = UserDocumentExtraction::query()->create([
            'user_document_id' => $document->id,
            'user_id' => $user->id,
            'document_type_code' => 'CV',
            'status' => DocumentExtractionStatus::PendingReview,
            'draft_payload' => [
                'educations' => [
                    [
                        'degree' => 'Licence soins infirmiers',
                        'institution_name' => 'Ecole de Bafoussam',
                        'start_date' => '2003-07-01',
                    ],
                    [
                        'degree' => 'Diplôme aide-soignant',
                        'institution_name' => 'Centre de formation',
                        'start_date' => '2010-01-01',
                    ],
                ],
                'experiences' => [
                    [
                        'job_title' => 'Aide-Soignant',
                        'company_name' => 'Clinique Louis Pasteur Sarl',
                        'start_date' => '2014-02-01',
                    ],
                ],
                'languages' => [
                    [
                        'resolved_language_id' => $frenchId,
                        'resolved_language_level_id' => $intermediateLevelId,
                    ],
                    [
                        'resolved_language_id' => $englishId,
                        'resolved_language_level_id' => $beginnerLevelId,
                    ],
                ],
                'skills' => [
                    ['name' => 'Planification du travail', 'resolved_skill_id' => $skill->id],
                ],
            ],
        ]);

        $this->app->make(ApplyUserDocumentExtractionAction::class)->execute($extraction, $reviewer);

        self::assertSame(2, Education::query()->where('user_id', $user->id)->count());
        self::assertSame(1, Experience::query()->where('user_id', $user->id)->count());
        self::assertSame(1, UserLanguage::query()->where('user_id', $user->id)->where('language_id', $frenchId)->count());
        self::assertSame(2, UserLanguage::query()->where('user_id', $user->id)->count());
        self::assertSame(1, UserSkill::query()->where('user_id', $user->id)->where('skill_id', $skill->id)->count());
    }
}
