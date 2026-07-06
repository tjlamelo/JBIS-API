<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Intel;

use App\Core\Domain\Identity\Actions\Document\ProcessUserDocumentExtractionAction;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class ProcessUserDocumentExtractionActionTest extends TestCase
{
    public function test_processes_cv_image_into_pending_review_draft(): void
    {
        Config::set('ai.driver', 'fake');
        Config::set('ai.document_extraction.driver', 'fake');
        Config::set('ai.fake.structured_stub', [
            'notes' => 'ok',
            'user_profile' => [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'date_of_birth' => '1990-01-01',
                'place_of_birth' => 'Douala',
                'nationality_country_name' => 'Cameroun',
                'residence_city_name' => 'Yaoundé',
                'address' => '',
                'phone_number2' => '',
                'phone_number3' => '',
                'gender' => 'M',
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

        $user = User::factory()->create();
        $type = DocumentType::query()->where('code', 'CV')->first()
            ?? DocumentType::factory()->create(['code' => 'CV', 'label' => ['fr' => 'CV', 'en' => 'CV']]);

        $document = UserDocument::factory()->create([
            'user_id' => $user->id,
            'document_type_id' => $type->id,
            'mime_type' => 'image/jpeg',
            'file_path' => 'Document/users/'.$user->id.'/2026/06/cv.jpg',
        ]);

        $extraction = $this->app->make(ProcessUserDocumentExtractionAction::class)->execute($document->id);

        self::assertNotNull($extraction);
        self::assertSame(DocumentExtractionStatus::PendingReview, $extraction->status);
        self::assertSame('Jean', $extraction->draft_payload['user_profile']['first_name'] ?? null);
    }
}
