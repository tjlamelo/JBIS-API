<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\ApplicationStep;
use App\Core\Domain\Candidacy\Services\OfferApplicationReadinessService;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Candidacy\States\ApplicationStepStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Débloque les candidatures PENDING lorsque les documents obligatoires sont validés.
 */
final class ResumePendingApplicationsAction
{
    public function __construct(
        private readonly OfferApplicationReadinessService $offerReadiness,
        private readonly AttachOfferRequiredDocumentsAction $attachOfferDocuments,
    ) {}

    public function execute(User $user): void
    {
        $applications = Application::query()
            ->where('user_id', $user->id)
            ->where('status', ApplicationStatus::Pending->value)
            ->whereNotNull('offer_id')
            ->with(['offer.requiredDocuments', 'offer.program.requiredDocuments', 'steps'])
            ->get();

        foreach ($applications as $application) {
            $this->tryResume($application, $user);
        }
    }

    private function tryResume(Application $application, User $user): void
    {
        $offer = $application->offer;
        if (! $offer instanceof Offer) {
            return;
        }

        $readiness = $this->offerReadiness->assess($offer, $user);
        $pendingValidation = collect($readiness->required_documents)
            ->filter(fn (array $row): bool => ($row['is_mandatory'] ?? false) && ($row['satisfied'] ?? false))
            ->filter(fn (array $row): bool => ($row['user_document_status'] ?? null) === 'PENDING')
            ->count();
        $missingMandatory = collect($readiness->required_documents)
            ->filter(fn (array $row): bool => ($row['is_mandatory'] ?? false) && ! ($row['satisfied'] ?? false))
            ->count();

        if ($pendingValidation > 0 || $missingMandatory > 0) {
            return;
        }

        DB::transaction(function () use ($application, $user): void {
            $now = Carbon::now();
            $first = $application->steps()->orderBy('step_order')->first();

            if ($first instanceof ApplicationStep) {
                ApplicationStep::query()
                    ->whereKey($first->id)
                    ->update([
                        'status' => ApplicationStepStatus::Pending->value,
                        'updated_at' => $now,
                    ]);

                $application->update([
                    'status' => ApplicationStatus::InProgress->value,
                    'current_application_step_id' => $first->id,
                ]);
            } else {
                $application->update([
                    'status' => ApplicationStatus::InProgress->value,
                ]);
            }

            $this->attachOfferDocuments->execute($application->fresh(), $user);
        });
    }
}
