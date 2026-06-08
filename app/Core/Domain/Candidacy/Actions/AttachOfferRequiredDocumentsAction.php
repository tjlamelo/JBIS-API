<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationDocument;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Workflow\States\ProcessStepType;
use Illuminate\Support\Collection;

final class AttachOfferRequiredDocumentsAction
{
    public function __construct(
        private readonly OfferApplicationReadinessService $readinessService,
    ) {}

    public function execute(Application $application, User $user): void
    {
        $application->loadMissing(['offer.requiredDocuments', 'offer.program.requiredDocuments', 'steps']);

        $offer = $application->offer;
        if ($offer === null) {
            return;
        }

        $readiness = $this->readinessService->assess($offer, $user);
        $targetStep = $this->resolveDocumentStep($application);

        foreach ($readiness->required_documents as $row) {
            if (! $row['satisfied'] || empty($row['user_document_id'])) {
                continue;
            }

            $userDocumentId = (int) $row['user_document_id'];

            ApplicationDocument::query()->updateOrCreate(
                [
                    'application_id' => $application->id,
                    'user_document_id' => $userDocumentId,
                ],
                [
                    'application_step_id' => $targetStep?->id,
                    'status' => 'PENDING',
                ],
            );
        }
    }

    private function resolveDocumentStep(Application $application): ?ApplicationStep
    {
        /** @var Collection<int, ApplicationStep> $steps */
        $steps = $application->steps;

        $documentStep = $steps->first(function (ApplicationStep $step): bool {
            $type = $step->step_type instanceof ProcessStepType
                ? $step->step_type
                : ProcessStepType::tryFrom((string) $step->step_type);

            return $type === ProcessStepType::DocumentCollection || (bool) $step->requires_documents;
        });

        return $documentStep ?? $steps->first();
    }
}
