<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\DTOs;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Models\ApplicationStepEvent;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use Illuminate\Support\Collection;

final readonly class ApplicationProgressView
{
    /**
     * @param  list<array<string, mixed>>  $steps
     * @param  list<array<string, mixed>>  $activityLog
     */
    public function __construct(
        public int $applicationId,
        public string $applicationNumber,
        public string $status,
        public int $processFlowId,
        public int $processFlowVersion,
        public string $flowGroupId,
        public ?string $offerLabel,
        public ?string $programLabel,
        public ?int $currentStepId,
        public ?int $currentStepOrder,
        public ?string $currentStepTitle,
        public int $completedStepsCount,
        public int $remainingStepsCount,
        public float $totalDue,
        public float $totalPaid,
        public float $totalRemaining,
        public bool $hasAcceptedProtocol,
        public array $steps,
        public array $activityLog,
    ) {}

    /**
     * @param  Collection<int, ApplicationStep>  $steps
     * @param  Collection<int, ApplicationStepEvent>  $events
     */
    public static function fromApplication(
        Application $application,
        string $locale,
        int $completedCount,
        int $remainingCount,
        ?ApplicationStep $current,
        Collection $steps,
        Collection $events,
    ): self {
        $pick = static function (array|string|null $field) use ($locale): ?string {
            if ($field === null) {
                return null;
            }
            if (is_string($field)) {
                return $field;
            }

            return $field[$locale] ?? $field['fr'] ?? $field['en'] ?? null;
        };

        $offer = $application->offer;
        $program = $application->program;

        $stepPayload = $steps->map(static function (ApplicationStep $step) use ($pick, $current): array {
            $status = $step->status instanceof ApplicationStepStatus
                ? $step->status->value
                : (string) $step->status;

            $documents = $step->relationLoaded('applicationDocuments')
                ? $step->applicationDocuments->map(static function ($doc) use ($pick): array {
                    $userDoc = $doc->userDocument;

                    return [
                        'id' => $doc->id,
                        'user_document_id' => $doc->user_document_id,
                        'status' => $doc->status,
                        'admin_notes' => $doc->admin_notes,
                        'reviewed_at' => $doc->reviewed_at?->toIso8601String(),
                        'document_type' => $userDoc?->documentType ? [
                            'id' => $userDoc->documentType->id,
                            'name' => $pick($userDoc->documentType->name),
                        ] : null,
                    ];
                })->values()->all()
                : [];

            $installments = $step->relationLoaded('installments')
                ? $step->installments->map(static fn ($i): array => [
                    'id' => $i->id,
                    'amount' => (float) $i->amount,
                    'currency' => $i->currency,
                    'status' => $i->status,
                    'due_date' => $i->due_date?->toIso8601String(),
                    'paid_at' => $i->paid_at?->toIso8601String(),
                ])->values()->all()
                : [];

            $interview = $step->relationLoaded('interview') && $step->interview
                ? [
                    'id' => $step->interview->id,
                    'status' => $step->interview->status,
                    'result' => $step->interview->result,
                    'scheduled_date' => $step->interview->scheduled_date?->toIso8601String(),
                    'duration' => $step->interview->duration,
                    'interview_type' => $step->interview->interview_type,
                    'location' => $step->interview->location,
                    'interviewer_name' => $step->interview->interviewer_name,
                    'internal_notes' => $step->interview->internal_notes,
                    'candidate_feedback' => $step->interview->candidate_feedback,
                    'evaluation_criteria' => $step->interview->evaluation_criteria,
                    'salary_offered' => $step->interview->salary_offered !== null
                        ? (float) $step->interview->salary_offered
                        : null,
                    'salary_negotiated' => $step->interview->salary_negotiated !== null
                        ? (float) $step->interview->salary_negotiated
                        : null,
                    'work_conditions_notes' => $step->interview->work_conditions_notes,
                ]
                : null;

            return [
                'id' => $step->id,
                'step_order' => $step->step_order,
                'section_key' => $step->section_key,
                'step_type' => $step->step_type instanceof \BackedEnum
                    ? $step->step_type->value
                    : (string) $step->step_type,
                'title' => $pick($step->title),
                'status' => $status,
                'amount_due' => (float) $step->amount_due,
                'amount_paid' => (float) $step->amount_paid,
                'amount_remaining' => $step->amountRemaining(),
                'payment_status' => $step->payment_status instanceof \BackedEnum
                    ? $step->payment_status->value
                    : (string) $step->payment_status,
                'requires_documents' => (bool) $step->requires_documents,
                'document_type_ids' => $step->document_type_ids ?? [],
                'documents_validated' => (bool) $step->documents_validated,
                'interview_passed' => $step->interview_passed,
                'is_signed' => (bool) $step->is_signed,
                'is_current' => $current !== null && $current->id === $step->id,
                'documents' => $documents,
                'installments' => $installments,
                'interview' => $interview,
            ];
        })->values()->all();

        $activityLog = $events->map(static function (ApplicationStepEvent $event): array {
            $actor = $event->actor;

            return [
                'id' => $event->id,
                'action' => $event->action,
                'application_step_id' => $event->application_step_id,
                'actor' => $actor ? [
                    'id' => $actor->id,
                    'first_name' => $actor->first_name,
                    'last_name' => $actor->last_name,
                ] : null,
                'meta' => $event->meta,
                'created_at' => $event->created_at?->toIso8601String(),
            ];
        })->values()->all();

        return new self(
            applicationId: $application->id,
            applicationNumber: $application->application_number,
            status: $application->status instanceof \BackedEnum
                ? $application->status->value
                : (string) $application->status,
            processFlowId: (int) $application->process_flow_id,
            processFlowVersion: (int) $application->process_flow_version,
            flowGroupId: (string) $application->flow_group_id,
            offerLabel: $offer ? $pick($offer->title) : null,
            programLabel: $program ? $pick($program->name) : null,
            currentStepId: $current?->id,
            currentStepOrder: $current?->step_order,
            currentStepTitle: $current ? $pick($current->title) : null,
            completedStepsCount: $completedCount,
            remainingStepsCount: $remainingCount,
            totalDue: (float) $application->total_due,
            totalPaid: (float) $application->total_paid,
            totalRemaining: $application->totalRemaining(),
            hasAcceptedProtocol: (bool) $application->has_accepted_protocol,
            steps: $stepPayload,
            activityLog: $activityLog,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'application_id' => $this->applicationId,
            'application_number' => $this->applicationNumber,
            'status' => $this->status,
            'process_flow_id' => $this->processFlowId,
            'process_flow_version' => $this->processFlowVersion,
            'flow_group_id' => $this->flowGroupId,
            'offer_label' => $this->offerLabel,
            'program_label' => $this->programLabel,
            'current_step_id' => $this->currentStepId,
            'current_step_order' => $this->currentStepOrder,
            'current_step_title' => $this->currentStepTitle,
            'completed_steps_count' => $this->completedStepsCount,
            'remaining_steps_count' => $this->remainingStepsCount,
            'total_due' => $this->totalDue,
            'total_paid' => $this->totalPaid,
            'total_remaining' => $this->totalRemaining,
            'has_accepted_protocol' => $this->hasAcceptedProtocol,
            'steps' => $this->steps,
            'activity_log' => $this->activityLog,
        ];
    }
}
