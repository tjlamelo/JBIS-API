<?php

namespace App\Core\Domain\Identity\Services;

use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDevice;
use App\Notifications\SuspiciousAccountActivityNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserDeviceSecurityService
{
    public function handleSuccessfulLogin(User $user, DeviceContextDto $context, string $tokenName = 'api'): void
    {
        $fingerprint = $this->fingerprint($context);
        $ip = $context->ip;
        $userAgent = Str::limit($context->userAgent, 1024, '');
        $deviceName = $context->deviceName !== '' ? $context->deviceName : 'Unknown device';

        $device = UserDevice::query()->firstOrNew([
            'user_id' => $user->id,
            'device_key' => $fingerprint,
        ]);

        $isNewDevice = ! $device->exists;
        $hasKnownDevices = $user->devices()->exists();
        $riskFlags = [];
        $riskScore = 0;

        if ($isNewDevice && $hasKnownDevices) {
            $riskFlags[] = 'new_device';
            $riskScore += 60;
        }

        if ($device->exists && $device->ip && $device->ip !== $ip) {
            $riskFlags[] = 'ip_changed';
            $riskScore += 30;
        }

        if ($device->exists && $device->user_agent && $device->user_agent !== $userAgent) {
            $riskFlags[] = 'agent_changed';
            $riskScore += 20;
        }

        $device->fill([
            'device_name' => $deviceName,
            'ip' => $ip,
            'last_ip' => $ip,
            'user_agent' => $userAgent,
            'last_login_at' => now(),
            'first_seen_at' => $device->first_seen_at ?? now(),
            'last_seen_at' => now(),
            'login_count' => ((int) $device->login_count) + 1,
            'risk_score' => $riskScore,
            'risk_flags' => $riskFlags,
            'last_token_name' => $tokenName,
            // Première connexion (aucun device connu) = trusted, pas suspecte.
            'is_trusted' => (! $hasKnownDevices && $isNewDevice) || $riskScore < 50,
        ]);
        $device->save();

        if ($riskScore >= 50) {
            $this->notifySuspicious($user, $device, 'Connexion inhabituelle detectee sur votre compte.');
        }
    }

    public function handleFailedLogin(User $user, DeviceContextDto $context): void
    {
        $ip = $context->ip;
        $key = sprintf('security:failed-login:%d:%s', $user->id, $ip);
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(30));

        if ($attempts >= 5) {
            $user->notify(new SuspiciousAccountActivityNotification(
                title: 'Plusieurs tentatives de connexion echouees ont ete detectees.',
                ip: $ip,
                device: Str::limit($context->userAgent, 160),
                flags: ['failed_logins', 'threshold_reached'],
            ));
            Cache::forget($key);
        }
    }

    private function notifySuspicious(User $user, UserDevice $device, string $title): void
    {
        if ($device->last_alerted_at && $device->last_alerted_at->gt(now()->subHours(12))) {
            return;
        }

        $user->notify(new SuspiciousAccountActivityNotification(
            title: $title,
            ip: (string) $device->ip,
            device: (string) ($device->device_name ?: Str::limit((string) $device->user_agent, 120)),
            flags: $device->risk_flags ?? [],
        ));

        $device->forceFill([
            'last_alerted_at' => now(),
        ])->save();
    }

    private function fingerprint(DeviceContextDto $context): string
    {
        $parts = [
            $context->secChUa,
            $context->secChUaPlatform,
            $context->acceptLanguage,
            $context->userAgent,
        ];

        return hash('sha256', implode('|', $parts));
    }
}
