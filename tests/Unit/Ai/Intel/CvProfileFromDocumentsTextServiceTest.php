<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Intel;

use App\Core\Domain\Shared\Ai\Intel\CvProfileFromDocumentsTextService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class CvProfileFromDocumentsTextServiceTest extends TestCase
{
    public function test_extract_draft_uses_fake_structured_stub(): void
    {
        Config::set('ai.driver', 'fake');
        Config::set('ai.fake.structured_stub', [
            'notes' => 'test',
            'user_profile' => [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'date_of_birth' => '',
                'place_of_birth' => '',
                'nationality_country_name' => '',
                'residence_city_name' => '',
                'address' => '',
                'phone_number2' => '',
                'phone_number3' => '',
                'gender' => '',
                'bio' => '',
                'marital_status' => '',
                'number_of_children' => 0,
                'email_institutional' => '',
            ],
            'educations' => [],
            'experiences' => [],
            'certifications' => [],
            'languages' => [],
            'formations' => [],
        ]);

        $draft = $this->app->make(CvProfileFromDocumentsTextService::class)
            ->extractDraft('Contenu CV fictif');

        self::assertSame('Ada', $draft['user_profile']['first_name']);
        self::assertSame('test', $draft['notes']);
    }
}
