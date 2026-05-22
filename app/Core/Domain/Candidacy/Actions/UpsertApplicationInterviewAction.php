<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Models\Interview;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Candidacy\Services\InterviewStepSyncService;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Carbon;

final class UpsertApplicationInterviewAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
        private readonly InterviewStepSyncService $interviewStepSync,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(ApplicationStep $step, array $data, int $actorUserId): Interview
    {
        $type = $step->step_type instanceof ProcessStepType
            ? $step->step_type
            : ProcessStepType::tryFrom((string) $step->step_type);

        if ($type !== ProcessStepType::Interview) {
            abort(422, __('Cette étape n\'est pas un entretien.'));
        }

        $interview = Interview::query()->firstOrNew([
            'application_id' => $step->application_id,
            'application_step_id' => $step->id,
        ]);

        $interview->application_id = $step->application_id;
        $interview->application_step_id = $step->id;

        foreach ([
            'scheduled_date',
            'duration',
            'interview_type',
            'location',
            'interviewer_name',
            'status',
            'result',
            'internal_notes',
            'candidate_feedback',
            'evaluation_criteria',
            'salary_offered',
            'salary_negotiated',
            'work_conditions_notes',
            'company_id',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $interview->{$field} = $data[$field];
            }
        }

        if (isset($data['scheduled_date']) && $data['scheduled_date'] !== null) {
            $interview->scheduled_date = Carbon::parse((string) $data['scheduled_date']);
        }

        $interview->save();

        if (array_key_exists('interview_passed', $data)) {
            $passed = $data['interview_passed'];
            $now = Carbon::now();
            $step->update([
                'interview_passed' => $passed,
                'interview_validated_at' => $passed !== null ? $now : null,
                'interview_validated_by' => $passed !== null ? $actorUserId : null,
            ]);
            $this->interviewStepSync->syncFromStep($step->fresh(), $passed === null ? null : (bool) $passed);
        } elseif (isset($data['result'])) {
            $passed = match ($data['result']) {
                'PASSED' => true,
                'FAILED' => false,
                default => null,
            };
            if ($passed !== null) {
                $step->update([
                    'interview_passed' => $passed,
                    'interview_validated_at' => Carbon::now(),
                    'interview_validated_by' => $actorUserId,
                ]);
            }
        }

        $this->activityLogger->log(
            $step->application_id,
            ApplicationActivityLogger::ACTION_INTERVIEW_UPDATED,
            $step->id,
            $actorUserId,
            ['interview_id' => $interview->id],
        );

        return $interview->fresh();
    }
}
