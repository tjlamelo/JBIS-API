<?php

declare(strict_types=1);

namespace Tests\Unit\Candidacy;

use App\Core\Domain\Candidacy\Services\ApplicationStepSnapshotBuilder;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicationStepSnapshotBuilderTest extends TestCase
{
    #[Test]
    public function it_assigns_globally_unique_step_order_across_sections(): void
    {
        $flow = ProcessFlow::query()->create([
            'flow_group_id' => (string) Str::uuid(),
            'version' => 1,
            'status' => ProcessFlowStatus::Published,
            'name' => ['fr' => 'Flow test'],
        ]);

        $sectionA = ProcessFlowSection::query()->create([
            'process_flow_id' => $flow->id,
            'key' => 'file_opening',
            'title' => ['fr' => 'Ouverture'],
            'section_order' => 1,
        ]);

        $sectionB = ProcessFlowSection::query()->create([
            'process_flow_id' => $flow->id,
            'key' => 'procedure_start',
            'title' => ['fr' => 'Démarrage'],
            'section_order' => 2,
        ]);

        ProcessStep::query()->create([
            'process_flow_id' => $flow->id,
            'process_flow_section_id' => $sectionA->id,
            'step_order' => 1,
            'step_type' => ProcessStepType::DocumentCollection,
            'title' => ['fr' => 'Docs A1'],
            'is_blocking' => true,
            'is_required' => true,
        ]);

        ProcessStep::query()->create([
            'process_flow_id' => $flow->id,
            'process_flow_section_id' => $sectionA->id,
            'step_order' => 2,
            'step_type' => ProcessStepType::Payment,
            'title' => ['fr' => 'Paiement A2'],
            'is_blocking' => true,
            'is_required' => true,
            'default_amount' => 1000,
        ]);

        ProcessStep::query()->create([
            'process_flow_id' => $flow->id,
            'process_flow_section_id' => $sectionB->id,
            'step_order' => 1,
            'step_type' => ProcessStepType::Service,
            'title' => ['fr' => 'Service B1'],
            'is_blocking' => true,
            'is_required' => true,
        ]);

        $builder = new ApplicationStepSnapshotBuilder();
        $rows = $builder->buildRows(99, $flow->fresh(['sections', 'steps.section']), Carbon::parse('2026-01-01 10:00:00'));

        $this->assertCount(3, $rows);
        $this->assertSame([1, 2, 3], array_column($rows, 'step_order'));
        $this->assertSame('file_opening', $rows[0]['section_key']);
        $this->assertSame('file_opening', $rows[1]['section_key']);
        $this->assertSame('procedure_start', $rows[2]['section_key']);
    }
}
