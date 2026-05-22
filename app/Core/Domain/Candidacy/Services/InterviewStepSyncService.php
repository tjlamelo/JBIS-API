<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Models\Interview;
use App\Core\Domain\Workflow\States\ProcessStepType;

final class InterviewStepSyncService
{
    public function syncFromStep(ApplicationStep $step, ?bool $passed): void
    {
        $type = $step->step_type instanceof ProcessStepType
            ? $step->step_type
            : ProcessStepType::tryFrom((string) $step->step_type);

        if ($type !== ProcessStepType::Interview) {
            return;
        }

        $interview = Interview::query()->firstOrNew([
            'application_id' => $step->application_id,
            'application_step_id' => $step->id,
        ]);

        $interview->application_id = $step->application_id;
        $interview->application_step_id = $step->id;

        if ($passed === true) {
            $interview->result = 'PASSED';
            $interview->status = 'COMPLETED';
        } elseif ($passed === false) {
            $interview->result = 'FAILED';
            $interview->status = 'COMPLETED';
        } else {
            $interview->result = 'PENDING';
            $interview->status = 'SCHEDULED';
        }

        $interview->save();
    }
}
