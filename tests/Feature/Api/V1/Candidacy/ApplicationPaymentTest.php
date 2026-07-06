<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Candidacy;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Finance\Models\Payment;
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

class ApplicationPaymentTest extends TestCase
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
    public function candidate_can_declare_payment_as_pending(): void
    {
        [$offer, $candidate, $applicationId, $paymentStepId] = $this->makeApplicationOnPaymentStep();

        Sanctum::actingAs($candidate);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/payments/declare", [
            'amount' => 50_000,
            'payment_type' => 'FULL',
            'payment_method' => 'BANK_TRANSFER',
            'reference' => 'VIR-12345',
        ])
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.payment_status', 'UNPAID');

        $this->assertDatabaseHas('payments', [
            'application_id' => $applicationId,
            'application_step_id' => $paymentStepId,
            'amount' => 50_000,
            'status' => 'PENDING',
            'reference' => 'VIR-12345',
        ]);
    }

    #[Test]
    public function admin_confirms_pending_payment_and_step_becomes_paid(): void
    {
        [$offer, $candidate, $applicationId, $paymentStepId] = $this->makeApplicationOnPaymentStep();

        Sanctum::actingAs($candidate);
        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/payments/declare", [
            'amount' => 50_000,
            'payment_type' => 'FULL',
            'reference' => 'VIR-CONFIRM',
        ])->assertOk();

        $paymentId = (int) Payment::query()
            ->where('application_step_id', $paymentStepId)
            ->where('status', 'PENDING')
            ->value('id');

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/payments/{$paymentId}/confirm")
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.payment_status', 'PAID')
            ->assertJsonPath('data.application.steps.0.amount_paid', 50_000);

        $this->assertDatabaseHas('payments', [
            'id' => $paymentId,
            'status' => 'COMPLETED',
        ]);

        $this->assertDatabaseHas('application_steps', [
            'id' => $paymentStepId,
            'payment_status' => 'PAID',
            'amount_paid' => 50_000,
        ]);
    }

    #[Test]
    public function advance_is_blocked_when_payment_step_is_unpaid(): void
    {
        [$offer, $candidate, $applicationId, $paymentStepId] = $this->makeApplicationOnPaymentStep();

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/advance", [
            'force' => false,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Le paiement de cette étape doit être soldé avant de continuer.');
    }

    #[Test]
    public function admin_can_waive_payment_and_advance_step(): void
    {
        [$offer, $candidate, $applicationId, $paymentStepId] = $this->makeApplicationOnPaymentStep();

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/payments/waive", [
            'reason' => 'Bourse intégrale',
        ])
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.payment_status', 'WAIVED');

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/advance")
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.status', 'COMPLETED');
    }

    #[Test]
    public function admin_can_record_payment_directly_and_advance(): void
    {
        [$offer, $candidate, $applicationId, $paymentStepId] = $this->makeApplicationOnPaymentStep();

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/payments", [
            'amount' => 50_000,
            'payment_type' => 'FULL',
            'status' => 'COMPLETED',
            'payment_method' => 'CASH',
            'reference' => 'RECU-001',
        ])
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.payment_status', 'PAID');

        $this->postJson("/api/v1/candidacy/applications/{$applicationId}/steps/{$paymentStepId}/advance")
            ->assertOk()
            ->assertJsonPath('data.application.steps.0.status', 'COMPLETED');
    }

    /**
     * @return array{0: Offer, 1: User, 2: int, 3: int}
     */
    private function makeApplicationOnPaymentStep(): array
    {
        $passportType = DocumentType::query()->where('code', 'PASSPORT')->first()
            ?? DocumentType::query()->create([
                'code' => 'PASSPORT_TEST_PAYMENT',
                'label' => json_encode(['fr' => 'Passeport']),
                'storage_slug' => 'passeport',
                'is_active' => true,
                'visible_to_candidates' => true,
            ]);

        $required = RequiredDocument::query()->firstOrCreate(
            ['slug' => 'passeport-valide-payment'],
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
            'name' => ['fr' => 'Flow payment test'],
            'offer_id' => $offer->id,
            'flow_group_id' => (string) Str::uuid(),
            'version' => 1,
            'status' => ProcessFlowStatus::Published,
            'country_id' => $offer->country_id,
        ]);

        ProcessStep::query()->create([
            'process_flow_id' => $flow->id,
            'step_order' => 1,
            'step_type' => ProcessStepType::Payment,
            'title' => ['fr' => 'Frais de dossier'],
            'is_blocking' => true,
            'is_required' => true,
            'requires_documents' => false,
            'default_amount' => 50_000,
            'sla_alert_days' => 14,
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
            'status' => 'APPROVED',
        ]);

        Sanctum::actingAs($candidate);
        $response = $this->postJson('/api/v1/candidacy/applications', ['offer_id' => $offer->id]);
        $response->assertStatus(201);

        $applicationId = (int) ($response->json('data.application.application_id') ?? $response->json('data.application.id'));
        $paymentStepId = (int) ApplicationStep::query()
            ->where('application_id', $applicationId)
            ->where('step_order', 1)
            ->value('id');

        return [$offer, $candidate, $applicationId, $paymentStepId];
    }
}
