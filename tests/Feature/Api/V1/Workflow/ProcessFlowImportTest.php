<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Workflow;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Workflow\Import\ProcessFlowImportCoordinator;
use App\Core\Domain\Workflow\Import\ProcessFlowImportTemplateGenerator;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProcessFlowImportTest extends TestCase
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
    public function dry_run_valid_json_does_not_write_to_database(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $jsonPath = $this->writeJson($payload);

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile($jsonPath, 'json', commit: false);

        $this->assertTrue($result->success);
        $this->assertFalse($result->committed);
        $this->assertSame([], $result->createdFlowIds);
        $this->assertDatabaseMissing('process_flows', ['import_key' => $payload['flow_key']]);
    }

    #[Test]
    public function commit_valid_json_creates_draft_flow_with_pivot_documents(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $jsonPath = $this->writeJson($payload);

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile($jsonPath, 'json', commit: true);

        $this->assertTrue($result->success);
        $this->assertTrue($result->committed);
        $this->assertCount(1, $result->createdFlowIds);

        $flow = ProcessFlow::query()->findOrFail($result->createdFlowIds[0]);
        $this->assertSame(ProcessFlowStatus::Draft, $flow->status);
        $this->assertSame($payload['flow_key'], $flow->import_key);
        $this->assertSame(1, $flow->version);

        $step = ProcessStep::query()->where('process_flow_id', $flow->id)->where('step_order', 1)->firstOrFail();
        $this->assertNull($step->document_type_ids);
        $this->assertDatabaseHas('process_step_document_type', [
            'process_step_id' => $step->id,
            'document_type_id' => DocumentType::query()->where('code', 'PASSPORT')->value('id'),
        ]);
    }

    #[Test]
    public function unknown_document_type_code_returns_explicit_error(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $payload['sections'][0]['steps'][0]['required_documents'] = ['UNKNOWN_DOC_XYZ'];

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile(
            $this->writeJson($payload),
            'json',
            commit: false,
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('UNKNOWN_DOC_XYZ', $result->issues[0]->message);
    }

    #[Test]
    public function empty_sections_returns_validation_error(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $payload['sections'] = [];

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile(
            $this->writeJson($payload),
            'json',
            commit: false,
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('section', strtolower($result->issues[0]->message));
    }

    #[Test]
    public function existing_flow_key_creates_next_version_without_touching_published(): void
    {
        $this->seedImportFixtures();
        $country = Country::query()->where('code', 'DE')->firstOrFail();
        $flowKey = $this->uniqueFlowKey();

        $published = new ProcessFlow([
            'flow_group_id' => (string) \Illuminate\Support\Str::uuid(),
            'import_key' => $flowKey,
            'version' => 1,
            'status' => ProcessFlowStatus::Published->value,
            'country_id' => $country->id,
        ]);
        $published->setTranslations('name', ['fr' => 'Ancien', 'en' => 'Old']);
        $published->save();

        $jsonPath = $this->writeJson($this->validJsonPayload($flowKey));
        $result = app(ProcessFlowImportCoordinator::class)->importFromFile($jsonPath, 'json', commit: true);

        $this->assertTrue($result->success);
        $newFlow = ProcessFlow::query()->findOrFail($result->createdFlowIds[0]);

        $this->assertSame($published->flow_group_id, $newFlow->flow_group_id);
        $this->assertSame(2, $newFlow->version);
        $this->assertSame(ProcessFlowStatus::Draft, $newFlow->status);

        $published->refresh();
        $this->assertSame(ProcessFlowStatus::Published, $published->status);
    }

    #[Test]
    public function excel_template_import_dry_run_succeeds(): void
    {
        $this->seedImportFixtures();
        $templatePath = app(ProcessFlowImportTemplateGenerator::class)->generate(
            storage_path('app/templates/test-process-flow-import.xlsx'),
        );

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile($templatePath, 'excel', commit: false);

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->summaries);
    }

    #[Test]
    public function excel_template_includes_catalog_sheets_with_document_types(): void
    {
        $this->seedImportFixtures();
        $templatePath = app(ProcessFlowImportTemplateGenerator::class)->generate(
            storage_path('app/templates/test-process-flow-catalog.xlsx'),
        );

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $documentSheet = null;
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getTitle() === '_DocumentTypes') {
                $documentSheet = $worksheet;
                break;
            }
        }

        $this->assertNotNull($documentSheet);
        $rows = $documentSheet->toArray();
        $codes = array_map(static fn (mixed $row): string => strtoupper((string) ($row[0] ?? '')), array_slice($rows, 1));
        $this->assertContains('PASSPORT', $codes);
        $this->assertContains('CV', $codes);
        $this->assertContains('DIPLOMA', $codes);

        $instructions = $spreadsheet->getSheetByName('Instructions');
        $this->assertNotNull($instructions);
        $instructionText = implode(' ', array_map(
            static fn (array $row): string => implode(' ', array_map(static fn (mixed $cell): string => (string) $cell, $row)),
            $instructions->toArray(),
        ));
        $this->assertStringContainsString('total_procedure_fees', $instructionText);
        $this->assertStringContainsString('_DocumentTypes', $instructionText);
    }

    #[Test]
    public function mismatched_total_procedure_fees_is_rejected(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $payload['total_procedure_fees'] = 999999;

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile(
            $this->writeJson($payload),
            'json',
            commit: false,
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('total_procedure_fees', $result->issues[0]->message);
    }

    #[Test]
    public function section_key_is_generated_from_title_when_omitted(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        unset($payload['sections'][0]['section_key']);

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile(
            $this->writeJson($payload),
            'json',
            commit: true,
        );

        $this->assertTrue($result->success);
        $flow = ProcessFlow::query()->findOrFail($result->createdFlowIds[0]);
        $this->assertDatabaseHas('process_flow_sections', [
            'process_flow_id' => $flow->id,
            'key' => 'ouverture',
        ]);
    }

    #[Test]
    public function status_field_in_file_is_ignored_with_warning(): void
    {
        $this->seedImportFixtures();
        $payload = $this->validJsonPayload();
        $payload['status'] = 'published';

        $result = app(ProcessFlowImportCoordinator::class)->importFromFile(
            $this->writeJson($payload),
            'json',
            commit: false,
        );

        $this->assertTrue($result->success);
        $this->assertCount(1, $result->warnings);
        $this->assertStringContainsString('status', $result->warnings[0]->field);
    }

    #[Test]
    public function commit_via_api_sets_imported_by_admin(): void
    {
        $this->seedImportFixtures();
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $payload = $this->validJsonPayload();
        $jsonPath = $this->writeJson($payload);

        $this->postJson('/api/v1/catalog/admin/process-flows/import', [
            'file' => new \Illuminate\Http\UploadedFile($jsonPath, 'flow.json', 'application/json', null, true),
            'format' => 'json',
            'commit' => true,
        ])->assertOk();

        $flow = ProcessFlow::query()->where('import_key', $payload['flow_key'])->firstOrFail();
        $this->assertSame($admin->id, $flow->imported_by);
    }

    #[Test]
    public function admin_api_import_endpoint_supports_dry_run(): void
    {
        $this->seedImportFixtures();
        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $jsonPath = $this->writeJson($this->validJsonPayload());

        $this->postJson('/api/v1/catalog/admin/process-flows/import', [
            'file' => new \Illuminate\Http\UploadedFile($jsonPath, 'flow.json', 'application/json', null, true),
            'format' => 'json',
            'commit' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.import.success', true)
            ->assertJsonPath('data.import.committed', false);
    }

    private function seedImportFixtures(): void
    {
        Country::query()->firstOrCreate(
            ['code' => 'DE'],
            ['name' => ['fr' => 'Allemagne', 'en' => 'Germany'], 'phone_code' => '+49', 'is_active' => true],
        );

        foreach (['PASSPORT', 'CV', 'DIPLOMA'] as $code) {
            DocumentType::query()->firstOrCreate(
                ['code' => $code],
                [
                    'label' => json_encode(['fr' => $code]),
                    'storage_slug' => strtolower($code),
                    'is_active' => true,
                    'visible_to_candidates' => true,
                ],
            );
        }
    }

    private function uniqueFlowKey(): string
    {
        return 'import-test-flow-'.uniqid();
    }

    /**
     * @return array<string, mixed>
     */
    private function validJsonPayload(?string $flowKey = null): array
    {
        return [
            'flow_key' => $flowKey ?? $this->uniqueFlowKey(),
            'country_code' => 'DE',
            'name' => ['fr' => 'Parcours test import', 'en' => 'Import test flow'],
            'file_opening_fee' => 100000,
            'sections' => [
                [
                    'section_key' => 'ouverture',
                    'title' => ['fr' => 'Ouverture'],
                    'order' => 1,
                    'steps' => [
                        [
                            'step_order' => 1,
                            'step_type' => 'DOCUMENT_COLLECTION',
                            'title' => ['fr' => 'Dépôt documents'],
                            'is_blocking' => true,
                            'is_required' => true,
                            'required_documents' => ['PASSPORT', 'CV'],
                        ],
                        [
                            'step_order' => 2,
                            'step_type' => 'PAYMENT',
                            'payment_type' => 'FILE_OPENING',
                            'amount' => 100000,
                            'title' => ['fr' => 'Frais'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function writeJson(array $payload): string
    {
        $path = storage_path('app/testing/process-flow-import-'.uniqid().'.json');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return $path;
    }
}
