<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Application\Api\Support\TranslatableColumnResolver;
use App\Core\Domain\Candidacy\DTOs\OfferApplicationReadiness;
use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Candidacy\States\ApplicationStatus;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use Illuminate\Support\Collection;

final class OfferApplicationReadinessService
{
    public function __construct(
        private readonly RequiredDocumentTypeMapper $documentTypeMapper,
        private readonly PublishedProcessFlowResolver $processFlowResolver,
    ) {}

    public function assess(Offer $offer, User $user): OfferApplicationReadiness
    {
        $offer->loadMissing(['requiredDocuments', 'program.requiredDocuments']);

        $blockingReasons = [];
        $offerStatus = $offer->status instanceof OfferStatus ? $offer->status->value : (string) $offer->status;
        $accepting = $this->offerAcceptsApplications($offer);

        if (! $accepting) {
            $blockingReasons[] = match ($offerStatus) {
                OfferStatus::Draft->value => __('Cette offre n\'est pas encore publiée.'),
                OfferStatus::Closed->value => __('Cette offre n\'accepte plus de candidatures.'),
                OfferStatus::Archived->value => __('Cette offre est archivée.'),
                default => $this->isExpired($offer)
                    ? __('La date limite de candidature est dépassée.')
                    : __('Cette offre n\'accepte pas de candidatures pour le moment.'),
            };
        }

        if ($offer->available_positions !== null && (int) $offer->available_positions <= 0) {
            $blockingReasons[] = __('Tous les postes pour cette offre ont été pourvus.');
            $accepting = false;
        }

        $existing = Application::query()
            ->where('user_id', $user->id)
            ->where('offer_id', $offer->id)
            ->whereIn('status', [
                ApplicationStatus::Pending->value,
                ApplicationStatus::InProgress->value,
            ])
            ->latest('id')
            ->first();

        if ($existing !== null) {
            $blockingReasons[] = __('Vous avez déjà une candidature en cours pour cette offre.');
        }

        $requiredRows = $this->collectRequiredDocuments($offer);
        $userDocuments = UserDocument::query()
            ->where('user_id', $user->id)
            ->with('documentType:id,code,storage_slug')
            ->get();

        $documentChecks = [];
        $missingMandatory = 0;
        $pendingValidation = 0;

        foreach ($requiredRows as $required) {
            $check = $this->evaluateRequiredDocument($required, $userDocuments);
            $documentChecks[] = $check;

            if ($check['is_mandatory'] && ! $check['satisfied']) {
                $missingMandatory++;
            }

            if ($check['is_mandatory'] && $check['satisfied'] && $check['user_document_status'] === UserDocumentStatus::Pending->value) {
                $pendingValidation++;
            }
        }

        if ($missingMandatory > 0) {
            $blockingReasons[] = __('Des documents obligatoires manquent dans votre dossier. Téléversez-les avant de postuler.');
        }

        $flow = $this->processFlowResolver->resolve($offer->id, $offer->program_id, $offer->country_id);
        if ($flow === null) {
            $blockingReasons[] = __('Aucun parcours de candidature n\'est configuré pour cette offre.');
        }

        $recommendedStatus = ($pendingValidation > 0 && $missingMandatory === 0)
            ? ApplicationStatus::Pending->value
            : ApplicationStatus::InProgress->value;

        $canApply = $accepting
            && $existing === null
            && $missingMandatory === 0
            && $flow !== null;

        return new OfferApplicationReadiness(
            can_apply: $canApply,
            offer_status: $offerStatus,
            offer_accepting_applications: $accepting,
            existing_application: $existing ? [
                'id' => $existing->id,
                'application_number' => $existing->application_number,
                'status' => $existing->status instanceof ApplicationStatus
                    ? $existing->status->value
                    : (string) $existing->status,
            ] : null,
            required_documents: $documentChecks,
            blocking_reasons: array_values(array_unique($blockingReasons)),
            recommended_application_status: $recommendedStatus,
            has_process_flow: $flow !== null,
        );
    }

    private function offerAcceptsApplications(Offer $offer): bool
    {
        $status = $offer->status instanceof OfferStatus ? $offer->status : OfferStatus::tryFrom((string) $offer->status);

        if ($status !== OfferStatus::Published) {
            return false;
        }

        return ! $this->isExpired($offer);
    }

    private function isExpired(Offer $offer): bool
    {
        return $offer->expiration_date !== null && $offer->expiration_date->isPast();
    }

    /**
     * @return Collection<int, RequiredDocument>
     */
    private function collectRequiredDocuments(Offer $offer): Collection
    {
        $docs = $offer->requiredDocuments->concat($offer->program?->requiredDocuments ?? collect());

        return $docs->unique('id')->sortBy(fn (RequiredDocument $doc) => (int) ($doc->pivot->sort_order ?? 0))->values();
    }

    /**
     * @param  Collection<int, UserDocument>  $userDocuments
     * @return array<string, mixed>
     */
    private function evaluateRequiredDocument(RequiredDocument $required, Collection $userDocuments): array
    {
        $documentType = $this->documentTypeMapper->resolveDocumentType($required);
        $documentTypeId = $documentType?->id;

        $match = $documentTypeId !== null
            ? $this->findBestUserDocument($userDocuments, $documentTypeId)
            : null;

        $satisfied = $match !== null && $this->isUserDocumentUsable($match);

        return [
            'required_document_id' => $required->id,
            'name' => TranslatableColumnResolver::resolve($required->name),
            'slug' => $required->slug,
            'description' => TranslatableColumnResolver::resolve($required->description),
            'is_mandatory' => (bool) ($required->pivot->is_mandatory ?? true),
            'sort_order' => (int) ($required->pivot->sort_order ?? 0),
            'document_type_id' => $documentTypeId,
            'document_type_code' => $documentType?->code,
            'satisfied' => $satisfied,
            'user_document_id' => $match?->id,
            'user_document_status' => $match?->status instanceof UserDocumentStatus
                ? $match->status->value
                : ($match?->status !== null ? (string) $match->status : null),
        ];
    }

    /**
     * @param  Collection<int, UserDocument>  $userDocuments
     */
    private function findBestUserDocument(Collection $userDocuments, int $documentTypeId): ?UserDocument
    {
        return $userDocuments
            ->where('document_type_id', $documentTypeId)
            ->sortByDesc('id')
            ->first(fn (UserDocument $doc) => $this->isUserDocumentUsable($doc) || $doc->status !== UserDocumentStatus::Rejected);
    }

    private function isUserDocumentUsable(UserDocument $document): bool
    {
        if (! is_string($document->file_path) || $document->file_path === '') {
            return false;
        }

        $status = $document->status instanceof UserDocumentStatus
            ? $document->status
            : UserDocumentStatus::tryFrom((string) $document->status);

        if (in_array($status, [UserDocumentStatus::Rejected, UserDocumentStatus::Expired], true)) {
            return false;
        }

        if ($document->expiry_date !== null && $document->expiry_date->isPast()) {
            return false;
        }

        return true;
    }
}
