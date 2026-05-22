<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\Security;

use App\Core\Domain\Identity\DTOs\LoginRiskAssessmentDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDevice;
use App\Core\Domain\Identity\Models\UserSecurityEvent;

final class RecordUserSecurityEventAction
{
    public function execute(
        User $user,
        string $event,
        LoginRiskAssessmentDto $assessment,
        ?UserDevice $device = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?array $meta = null,
    ): UserSecurityEvent {
        return UserSecurityEvent::query()->create([
            'user_id' => $user->id,
            'user_device_id' => $device?->id,
            'event' => $event,
            'risk_score' => $assessment->score,
            'risk_level' => $assessment->level->value,
            'signals' => $assessment->signals,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }
}
