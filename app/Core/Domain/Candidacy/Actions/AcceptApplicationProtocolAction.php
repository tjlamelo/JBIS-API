<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Actions;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Candidacy\Services\ApplicationActivityLogger;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class AcceptApplicationProtocolAction
{
    public function __construct(
        private readonly ApplicationActivityLogger $activityLogger,
    ) {}

    public function execute(Application $application, User $user, ?Request $request = null): Application
    {
        if ((bool) $application->has_accepted_protocol) {
            return $application;
        }

        $application->update([
            'has_accepted_protocol' => true,
            'protocol_accepted_at' => Carbon::now(),
            'protocol_acceptance_ip' => $request?->ip(),
        ]);

        $this->activityLogger->log(
            (int) $application->id,
            'protocol.accepted',
            null,
            (int) $user->id,
        );

        return $application->fresh(['steps', 'currentStep', 'offer', 'program']);
    }
}
