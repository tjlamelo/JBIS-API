<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services;

use App\Core\Domain\Identity\Actions\Security\RecordUserSecurityEventAction;
use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\DTOs\LoginRiskAssessmentDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDevice;
use App\Notifications\SuspiciousAccountActivityNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserDeviceSecurityService
{
    public function __construct(
        private readonly LoginRiskEvaluator $riskEvaluator,
        private readonly RecordUserSecurityEventAction $recordSecurityEvent,
    ) {}

    public function assessLogin(User $user, DeviceContextDto $context): LoginRiskAssessmentDto
    {
        $fingerprint = $this->fingerprint($context);

        $knownDevice = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('device_key', $fingerprint)
            ->first();

        return $this->riskEvaluator->evaluate($user, $context, $knownDevice);
    }

    public function handleSuccessfulLogin(
        User $user,
        DeviceContextDto $context,
        LoginRiskAssessmentDto $assessment,
        string $tokenName = 'api',
        ?int $personalAccessTokenId = null,
    ): UserDevice {
        $this->riskEvaluator->recordLoginAttempt($user->id, $context->ip);

        $fingerprint = $this->fingerprint($context);
        $ip = $context->ip;
        $userAgent = Str::limit($context->userAgent, 1024, '');
        $deviceName = $context->deviceName !== '' ? $context->deviceName : 'Unknown device';

        $device = UserDevice::query()->firstOrNew([
            'user_id' => $user->id,
            'device_key' => $fingerprint,
        ]);

        $device->fill([
            'device_name' => $deviceName,
            'ip' => $ip,
            'last_ip' => $ip,
            'user_agent' => $userAgent,
            'last_login_at' => now(),
            'first_seen_at' => $device->first_seen_at ?? now(),
            'last_seen_at' => now(),
            'login_count' => ((int) $device->login_count) + 1,
            'risk_score' => $assessment->score,
            'risk_level' => $assessment->level->value,
            'risk_flags' => $assessment->signals,
            'last_risk_assessment' => $assessment->toArray(),
            'last_token_name' => $tokenName,
            'personal_access_token_id' => $personalAccessTokenId,
            'is_trusted' => $assessment->trustDevice,
        ]);
        $device->save();

        $this->recordSecurityEvent->execute(
            user: $user,
            event: 'login_success',
            assessment: $assessment,
            device: $device,
            ip: $ip,
            userAgent: $userAgent,
        );

        if ($assessment->shouldNotify) {
            $this->notifySuspicious($user, $device, $assessment);
        }

        return $device;
    }

    public function handleFailedLogin(User $user, DeviceContextDto $context): void
    {
        $ip = $context->ip;
        $key = sprintf('security:failed-login:%d:%s', $user->id, $ip);
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(30));

        $assessment = $this->riskEvaluator->evaluate($user, $context, null);

        $this->recordSecurityEvent->execute(
            user: $user,
            event: 'login_failed',
            assessment: $assessment,
            ip: $ip,
            userAgent: Str::limit($context->userAgent, 512),
            meta: ['failed_attempts' => $attempts],
        );

        $failedCfg = (array) config('login_security.failed_attempts', []);
        if ($attempts >= (int) ($failedCfg['critical'] ?? 8)) {
            $user->notify(new SuspiciousAccountActivityNotification(
                title: 'Plusieurs tentatives de connexion échouées ont été détectées.',
                ip: $ip,
                device: Str::limit($context->userAgent, 160),
                flags: ['failed_logins', 'threshold_critical'],
                riskScore: $assessment->score,
                riskLevel: $assessment->level->value,
            ));
            Cache::forget($key);
        } elseif ($attempts >= (int) ($failedCfg['elevated'] ?? 3)) {
            $user->notify(new SuspiciousAccountActivityNotification(
                title: 'Tentatives de connexion échouées détectées.',
                ip: $ip,
                device: Str::limit($context->userAgent, 160),
                flags: ['failed_logins', 'threshold_elevated'],
                riskScore: $assessment->score,
                riskLevel: $assessment->level->value,
            ));
        }
    }

    public function handleBlockedLogin(User $user, DeviceContextDto $context, LoginRiskAssessmentDto $assessment): void
    {
        $this->recordSecurityEvent->execute(
            user: $user,
            event: 'login_blocked',
            assessment: $assessment,
            ip: $context->ip,
            userAgent: Str::limit($context->userAgent, 512),
        );

        $user->notify(new SuspiciousAccountActivityNotification(
            title: 'Une connexion à haut risque a été bloquée sur votre compte.',
            ip: $context->ip,
            device: Str::limit($context->userAgent, 160),
            flags: $assessment->signals,
            riskScore: $assessment->score,
            riskLevel: $assessment->level->value,
        ));
    }

    private function notifySuspicious(User $user, UserDevice $device, LoginRiskAssessmentDto $assessment): void
    {
        $cooldownHours = (int) config('login_security.alert_cooldown_hours', 12);

        if ($device->last_alerted_at && $device->last_alerted_at->gt(now()->subHours($cooldownHours))) {
            return;
        }

        $user->notify(new SuspiciousAccountActivityNotification(
            title: 'Connexion inhabituelle détectée sur votre compte.',
            ip: (string) $device->ip,
            device: (string) ($device->device_name ?: Str::limit((string) $device->user_agent, 120)),
            flags: $assessment->signals,
            riskScore: $assessment->score,
            riskLevel: $assessment->level->value,
        ));

        $device->forceFill(['last_alerted_at' => now()])->save();
    }

    private function fingerprint(DeviceContextDto $context): string
    {
        return hash('sha256', implode('|', [
            $context->secChUa,
            $context->secChUaPlatform,
            $context->acceptLanguage,
            $context->userAgent,
        ]));
    }
}
