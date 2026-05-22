<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Queries;

use App\Core\Domain\Candidacy\DTOs\ApplicationProgressView;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;

final class ApplicationProgressQuery
{
    public function forApplication(Application $application, string $locale = 'fr'): ApplicationProgressView
    {
        $application->loadMissing([
            'currentStep',
            'processFlow:id,version,flow_group_id,name',
            'offer:id,title',
            'program:id,name',
            'steps' => fn ($q) => $q->orderBy('step_order'),
            'steps.applicationDocuments.userDocument.documentType',
            'steps.installments',
            'steps.interview',
            'events.actor:id,first_name,last_name',
        ]);

        $steps = $application->steps;
        $current = $application->currentStep;
        $events = $application->events;

        $completed = $steps->where('status', ApplicationStepStatus::Completed)->count();
        $remaining = $steps->filter(
            fn ($s) => in_array($s->status, [ApplicationStepStatus::Locked, ApplicationStepStatus::Pending], true)
        );

        return ApplicationProgressView::fromApplication(
            $application,
            $locale,
            $completed,
            $remaining->count(),
            $current,
            $steps,
            $events,
        );
    }

    /**
     * Chargement minimal pour listes (dashboard candidat multi-dossiers).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Application>
     */
    public function listForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Application::query()
            ->where('user_id', $userId)
            ->with([
                'currentStep:id,application_id,step_order,title,status,step_type',
                'offer:id,title',
                'program:id,name',
            ])
            ->orderByDesc('updated_at')
            ->get();
    }
}
