<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Consent;

use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserConsent;
use App\Core\Domain\Identity\Support\ConsentType;
use Illuminate\Validation\ValidationException;

final class RecordUserConsentAction
{
    public function execute(
        User $user,
        string $type,
        string $version,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): UserConsent {
        if (! in_array($type, ConsentType::ALL, true)) {
            throw ValidationException::withMessages([
                'type' => [__('Type de consentement invalide.')],
            ]);
        }

        $document = LegalDocument::query()
            ->where('type', $type)
            ->where('version', $version)
            ->first();

        if (! $document) {
            throw ValidationException::withMessages([
                'version' => [__('Version du document introuvable.')],
            ]);
        }

        if ($document->effective_at->isFuture()) {
            throw ValidationException::withMessages([
                'version' => [__('Ce document n\'est pas encore en vigueur.')],
            ]);
        }

        return UserConsent::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'version' => $version,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1024) : null,
            'accepted_at' => now(),
        ]);
    }
}
