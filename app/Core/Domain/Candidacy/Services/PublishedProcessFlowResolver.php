<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Résout le parcours publié applicable à une inscription.
 * Priorité : process_flow_id explicite → offre → programme (offre) → pays (offre) → programme direct → pays.
 */
final class PublishedProcessFlowResolver
{
    public function resolve(?int $offerId, ?int $programId, ?int $countryId = null, ?int $processFlowId = null): ?ProcessFlow
    {
        if ($processFlowId !== null) {
            return $this->publishedQuery()->whereKey($processFlowId)->orderByDesc('version')->first();
        }

        if ($offerId !== null) {
            $offer = Offer::query()->select(['id', 'program_id', 'country_id'])->find($offerId);

            $flow = $this->publishedQuery()
                ->where('offer_id', $offerId)
                ->orderByDesc('version')
                ->first();

            if ($flow !== null) {
                return $flow;
            }

            if ($offer?->program_id) {
                $flow = $this->publishedQuery()
                    ->where('program_id', $offer->program_id)
                    ->orderByDesc('version')
                    ->first();

                if ($flow !== null) {
                    return $flow;
                }
            }

            if ($offer?->country_id) {
                $flow = $this->publishedQuery()
                    ->where('country_id', $offer->country_id)
                    ->orderByDesc('version')
                    ->first();

                if ($flow !== null) {
                    return $flow;
                }
            }
        }

        if ($programId !== null) {
            $flow = $this->publishedQuery()
                ->where('program_id', $programId)
                ->orderByDesc('version')
                ->first();

            if ($flow !== null) {
                return $flow;
            }
        }

        if ($countryId !== null) {
            return $this->publishedQuery()
                ->where('country_id', $countryId)
                ->orderByDesc('version')
                ->first();
        }

        return null;
    }

    /**
     * Charge sections + steps en une requête (évite N+1 à la création du dossier).
     */
    public function resolveWithSteps(
        ?int $offerId,
        ?int $programId,
        ?int $countryId = null,
        ?int $processFlowId = null,
    ): ?ProcessFlow {
        $flow = $this->resolve($offerId, $programId, $countryId, $processFlowId);

        if ($flow === null) {
            return null;
        }

        return ProcessFlow::query()
            ->with([
                'sections:id,process_flow_id,key',
                'steps' => fn ($q) => $q->orderBy('step_order'),
                'steps.section:id,key',
            ])
            ->find($flow->id);
    }

    /** @return Builder<ProcessFlow> */
    private function publishedQuery(): Builder
    {
        return ProcessFlow::query()->where('status', ProcessFlowStatus::Published->value);
    }
}
