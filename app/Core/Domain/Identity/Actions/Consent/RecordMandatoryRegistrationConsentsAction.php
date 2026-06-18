<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Consent;

use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserConsent;
use App\Core\Domain\Identity\Support\ConsentType;

/**
 * Enregistre les consentements obligatoires à l'inscription (CGU, confidentialité, cookies essentiels).
 * Le marketing reste opt-in séparé.
 */
final class RecordMandatoryRegistrationConsentsAction
{
    /** @var list<string> */
    private const MANDATORY_TYPES = [
        ConsentType::TERMS,
        ConsentType::PRIVACY,
        ConsentType::COOKIES,
    ];

    public function __construct(
        private readonly RecordUserConsentAction $recordUserConsent,
    ) {}

    public function execute(
        User $user,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        foreach (self::MANDATORY_TYPES as $type) {
            $current = LegalDocument::query()
                ->where('type', $type)
                ->where('is_current', true)
                ->first();

            if ($current === null || $current->effective_at->isFuture()) {
                continue;
            }

            $alreadyAccepted = UserConsent::query()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->where('version', $current->version)
                ->exists();

            if ($alreadyAccepted) {
                continue;
            }

            $this->recordUserConsent->execute(
                $user,
                $type,
                $current->version,
                $ipAddress,
                $userAgent,
            );
        }
    }
}
