<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Candidacy;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OfferApplicationReadinessTest extends TestCase
{
    private static bool $bootstrapped = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$bootstrapped) {
            if (Schema::hasTable('permissions') && Role::query()->count() === 0) {
                $this->seed(PermissionSeeder::class);
                $this->seed(RoleSeeder::class);
            }
            self::$bootstrapped = true;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function readiness_blocks_application_when_mandatory_documents_are_missing(): void
    {
        [$offer, $candidate] = $this->makeOfferWithRequiredPassport();

        Sanctum::actingAs($candidate);

        $this->getJson("/api/v1/candidacy/offers/{$offer->id}/readiness")
            ->assertOk()
            ->assertJsonPath('data.readiness.can_apply', false)
            ->assertJsonPath('data.readiness.required_documents.0.satisfied', false);

        $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id])
            ->assertStatus(422);
    }

    #[Test]
    public function readiness_blocks_application_when_email_is_not_verified(): void
    {
        [$offer, $candidate, $passportType] = $this->makeOfferWithRequiredPassport();

        $candidate->forceFill(['email_verified_at' => null])->save();

        UserDocument::query()->create([
            'user_id' => $candidate->id,
            'uploaded_by' => $candidate->id,
            'document_type_id' => $passportType->id,
            'file_path' => 'Document/users/'.$candidate->id.'/passport.pdf',
            'original_filename' => 'passport.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'status' => 'PENDING',
        ]);

        Sanctum::actingAs($candidate);

        $this->getJson("/api/v1/candidacy/offers/{$offer->id}/readiness")
            ->assertOk()
            ->assertJsonPath('data.readiness.can_apply', false)
            ->assertJsonFragment(['Vérifiez votre adresse e-mail avant de candidater.']);

        $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id])
            ->assertStatus(422);
    }

    #[Test]
    public function candidate_can_apply_when_mandatory_documents_are_uploaded(): void
    {
        [$offer, $candidate, $passportType] = $this->makeOfferWithRequiredPassport();

        UserDocument::query()->create([
            'user_id' => $candidate->id,
            'uploaded_by' => $candidate->id,
            'document_type_id' => $passportType->id,
            'file_path' => 'Document/users/'.$candidate->id.'/passport.pdf',
            'original_filename' => 'passport.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'status' => 'PENDING',
        ]);

        Sanctum::actingAs($candidate);

        $this->getJson("/api/v1/candidacy/offers/{$offer->id}/readiness")
            ->assertOk()
            ->assertJsonPath('data.readiness.can_apply', true)
            ->assertJsonPath('data.readiness.recommended_application_status', 'PENDING');

        $create = $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id]);
        $create->assertStatus(201)
            ->assertJsonPath('data.application.status', 'PENDING');

        $applicationId = (int) ($create->json('data.application.application_id') ?? $create->json('data.application.id'));
        $this->assertDatabaseHas('application_documents', [
            'application_id' => $applicationId,
        ]);
    }

    #[Test]
    public function pending_application_keeps_current_step_pointer_for_admin(): void
    {
        [$offer, $candidate, $passportType] = $this->makeOfferWithRequiredPassport();

        UserDocument::query()->create([
            'user_id' => $candidate->id,
            'uploaded_by' => $candidate->id,
            'document_type_id' => $passportType->id,
            'file_path' => 'Document/users/'.$candidate->id.'/passport.pdf',
            'original_filename' => 'passport.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'status' => 'PENDING',
        ]);

        Sanctum::actingAs($candidate);

        $create = $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id]);
        $create->assertStatus(201)
            ->assertJsonPath('data.application.status', 'PENDING');

        $applicationId = (int) ($create->json('data.application.application_id') ?? $create->json('data.application.id'));

        $this->assertDatabaseHas('applications', [
            'id' => $applicationId,
            'status' => 'PENDING',
        ]);

        $this->assertNotNull(
            \App\Core\Domain\Candidacy\Models\Application::query()->find($applicationId)?->current_application_step_id,
        );
    }

    #[Test]
    public function readiness_uses_only_offer_required_documents_not_program_inheritance(): void
    {
        [$offer, $candidate] = $this->makeOfferWithRequiredPassport();

        $programDoc = RequiredDocument::query()->firstOrCreate(
            ['slug' => 'preuve-de-fonds'],
            [
                'name' => json_encode(['fr' => 'Preuve de fonds']),
                'type' => 'PDF',
                'description' => 'Test',
            ],
        );

        $program = \App\Core\Domain\Catalog\Models\Program::query()->create([
            'name' => ['fr' => 'Programme test', 'en' => 'Test program'],
            'slug' => 'programme-test-'.Str::random(6),
            'status' => 'PUBLISHED',
        ]);
        $program->requiredDocuments()->sync([
            $programDoc->id => ['is_mandatory' => true, 'sort_order' => 1],
        ]);

        $offer->update(['program_id' => $program->id]);

        Sanctum::actingAs($candidate);

        $this->getJson("/api/v1/candidacy/offers/{$offer->id}/readiness")
            ->assertOk()
            ->assertJsonCount(1, 'data.readiness.required_documents')
            ->assertJsonPath('data.readiness.required_documents.0.slug', 'passeport-valide');
    }

    /**
     * @return array{0: Offer, 1: User, 2: DocumentType}
     */
    private function makeOfferWithRequiredPassport(): array
    {
        $passportType = DocumentType::query()->where('code', 'PASSPORT')->first()
            ?? DocumentType::query()->create([
                'code' => 'PASSPORT_TEST',
                'label' => json_encode(['fr' => 'Passeport']),
                'storage_slug' => 'passeport',
                'is_active' => true,
                'visible_to_candidates' => true,
            ]);

        $required = RequiredDocument::query()->firstOrCreate(
            ['slug' => 'passeport-valide'],
            [
                'name' => json_encode(['fr' => 'Passeport valide']),
                'type' => 'PDF',
                'description' => 'Test',
            ],
        );

        $offer = Offer::factory()->create([
            'status' => OfferStatus::Published,
            'expiration_date' => now()->addMonth(),
            'available_positions' => 2,
        ]);

        $offer->requiredDocuments()->sync([
            $required->id => ['is_mandatory' => true, 'sort_order' => 1],
        ]);

        $flow = ProcessFlow::query()->create([
            'name' => ['fr' => 'Flow test'],
            'offer_id' => $offer->id,
            'flow_group_id' => (string) Str::uuid(),
            'version' => 1,
            'status' => ProcessFlowStatus::Published,
            'country_id' => $offer->country_id,
        ]);

        ProcessStep::query()->create([
            'process_flow_id' => $flow->id,
            'step_order' => 1,
            'step_type' => ProcessStepType::Info,
            'title' => ['fr' => 'Bienvenue'],
            'is_blocking' => true,
            'is_required' => true,
            'requires_documents' => false,
            'default_amount' => 0,
        ]);

        $candidate = User::factory()->create();
        $candidate->assignRole(ApplicationRole::CANDIDATE);

        return [$offer, $candidate, $passportType];
    }
}
