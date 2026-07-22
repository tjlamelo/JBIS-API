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

class ApplicationLifecycleTest extends TestCase
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
    public function candidate_can_cancel_active_application(): void
    {
        [$offer, $candidate] = $this->makeOfferWithCandidateReadyToApply();
        $applicationId = $this->createApplicationFor($candidate, $offer);

        Sanctum::actingAs($candidate);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/cancel", [
            'reason' => 'Changement de projet',
        ])
            ->assertOk()
            ->assertJsonPath('data.application.status', 'CANCELLED');

        $this->assertDatabaseHas('applications', [
            'id' => $applicationId,
            'status' => 'CANCELLED',
        ]);
    }

    #[Test]
    public function admin_can_reject_active_application(): void
    {
        [$offer, $candidate] = $this->makeOfferWithCandidateReadyToApply();
        $applicationId = $this->createApplicationFor($candidate, $offer);

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/candidacy/admin/applications/{$applicationId}/reject", [
            'reason' => 'Profil non retenu',
        ])
            ->assertOk()
            ->assertJsonPath('data.application.status', 'REJECTED');

        $this->assertDatabaseHas('applications', [
            'id' => $applicationId,
            'status' => 'REJECTED',
        ]);
    }

    #[Test]
    public function candidate_can_accept_application_protocol(): void
    {
        [$offer, $candidate] = $this->makeOfferWithCandidateReadyToApply();
        $applicationId = $this->createApplicationFor($candidate, $offer);

        Sanctum::actingAs($candidate);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/accept-protocol")
            ->assertOk()
            ->assertJsonPath('data.application.has_accepted_protocol', true);

        $this->assertDatabaseHas('applications', [
            'id' => $applicationId,
            'has_accepted_protocol' => true,
        ]);
    }

    /**
     * @return array{0: Offer, 1: User}
     */
    private function makeOfferWithCandidateReadyToApply(): array
    {
        $passportType = DocumentType::query()->where('code', 'PASSPORT')->first()
            ?? DocumentType::query()->firstOrCreate(
                ['code' => 'PASSPORT_TEST_LIFECYCLE'],
                [
                'label' => json_encode(['fr' => 'Passeport']),
                'storage_slug' => 'passeport',
                'is_active' => true,
                'visible_to_candidates' => true,
                'allowed_extensions' => json_encode(DocumentType::defaultAllowedExtensions()),
                'allowed_mime_types' => json_encode(DocumentType::defaultAllowedMimeTypes()),
                'max_file_size_kb' => 10240,
                ],
            );

        $required = RequiredDocument::query()->firstOrCreate(
            ['slug' => 'passeport-valide-lifecycle'],
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
            'name' => ['fr' => 'Flow lifecycle test'],
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

        return [$offer, $candidate];
    }

    private function createApplicationFor(User $candidate, Offer $offer): int
    {
        Sanctum::actingAs($candidate);

        $response = $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id]);
        $response->assertStatus(201);

        return (int) ($response->json('data.application.application_id') ?? $response->json('data.application.id'));
    }
}
