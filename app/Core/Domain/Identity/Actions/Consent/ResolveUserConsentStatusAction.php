<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Consent;

use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserConsent;
use App\Core\Domain\Identity\Support\ConsentType;

final class ResolveUserConsentStatusAction
{
    /**
     * @return list<array{
     *   type: string,
     *   current_version: string|null,
     *   accepted_version: string|null,
     *   accepted_at: string|null,
     *   is_up_to_date: bool,
     *   requires_action: bool,
     *   document: array<string, mixed>|null
     * }>
     */
    public function execute(User $user): array
    {
        $latestByType = LegalDocument::query()
            ->where('is_current', true)
            ->get()
            ->keyBy('type');

        $latestConsents = UserConsent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('accepted_at')
            ->get()
            ->unique('type')
            ->keyBy('type');

        $result = [];

        foreach (ConsentType::ALL as $type) {
            $current = $latestByType->get($type);
            $accepted = $latestConsents->get($type);
            $isUpToDate = $current !== null
                && $accepted !== null
                && $accepted->version === $current->version;

            $requiresAction = $current !== null && (
                $accepted === null
                || (! $isUpToDate && $current->requires_reacceptance)
            );

            $result[] = [
                'type' => $type,
                'current_version' => $current?->version,
                'accepted_version' => $accepted?->version,
                'accepted_at' => $accepted?->accepted_at?->toIso8601String(),
                'is_up_to_date' => $isUpToDate,
                'requires_action' => $requiresAction,
                'document' => $current ? $this->documentSummary($current) : null,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function documentSummary(LegalDocument $document): array
    {
        $locale = app()->getLocale();

        return [
            'version' => $document->version,
            'title' => $document->getTranslation('title', $locale),
            'summary' => $document->summary,
            'effective_at' => $document->effective_at->toIso8601String(),
        ];
    }
}
