<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services;

use App\Core\Domain\Identity\DTOs\DeviceContextDto;
use App\Core\Domain\Identity\DTOs\LoginRiskAssessmentDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDevice;
use App\Core\Domain\Identity\Support\LoginRiskLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class LoginRiskEvaluator
{
    /**
     * @param  array<string, int|float|bool|array<string, mixed>>  $config
     */
    public function __construct(
        private array $config = [],
    ) {
        $this->config = $config !== [] ? $config : (array) config('login_security', []);
    }

    public function evaluate(User $user, DeviceContextDto $context, ?UserDevice $knownDevice = null): LoginRiskAssessmentDto
    {
        $weights = (array) ($this->config['weights'] ?? []);
        $signalScores = [];
        $signals = [];

        $fingerprint = $this->fingerprint($context);
        $isNewDevice = $knownDevice === null;
        $ip = $context->ip;

        if ($isNewDevice && $user->devices()->exists()) {
            $this->addSignal($signals, $signalScores, 'new_device', (int) ($weights['new_device'] ?? 55));
        }

        $knownSubnets = $this->knownIpSubnets($user);
        $currentSubnet = $this->ipSubnet($ip);

        if ($currentSubnet !== null && $knownSubnets !== [] && ! in_array($currentSubnet, $knownSubnets, true)) {
            $this->addSignal($signals, $signalScores, 'new_ip_subnet', (int) ($weights['new_ip_subnet'] ?? 25));
        }

        if ($knownDevice !== null && $knownDevice->ip && $knownDevice->ip !== $ip) {
            $this->addSignal($signals, $signalScores, 'ip_changed_on_known_device', (int) ($weights['ip_changed_on_known_device'] ?? 20));
        }

        if ($knownDevice !== null && $knownDevice->user_agent && $knownDevice->user_agent !== Str::limit($context->userAgent, 1024, '')) {
            $this->addSignal($signals, $signalScores, 'user_agent_changed', (int) ($weights['user_agent_changed'] ?? 15));
        }

        if ($this->looksLikeBrowser($context) && $context->secChUa === '') {
            $this->addSignal($signals, $signalScores, 'browser_headers_missing', (int) ($weights['browser_headers_missing'] ?? 10));
        }

        $failedAttempts = $this->failedAttemptsForIp($user->id, $ip);
        $failedCfg = (array) ($this->config['failed_attempts'] ?? []);
        if ($failedAttempts >= (int) ($failedCfg['critical'] ?? 8)) {
            $this->addSignal($signals, $signalScores, 'failed_attempts_critical', (int) ($weights['failed_attempts_critical'] ?? 40));
        } elseif ($failedAttempts >= (int) ($failedCfg['elevated'] ?? 3)) {
            $this->addSignal($signals, $signalScores, 'failed_attempts_elevated', (int) ($weights['failed_attempts_elevated'] ?? 20));
        }

        if ($this->hasRapidMultiIp($user->id)) {
            $this->addSignal($signals, $signalScores, 'rapid_multi_ip', (int) ($weights['rapid_multi_ip'] ?? 35));
        }

        if ($this->isUnusualHour()) {
            $this->addSignal($signals, $signalScores, 'unusual_hour', (int) ($weights['unusual_hour'] ?? 10));
        }

        if ($this->isDatacenterIp($ip)) {
            $this->addSignal($signals, $signalScores, 'datacenter_ip', (int) ($weights['datacenter_ip'] ?? 25));
        }

        if ($isNewDevice && $this->isLongAbsence($user)) {
            $this->addSignal($signals, $signalScores, 'long_absence_new_device', (int) ($weights['long_absence_new_device'] ?? 15));
        }

        $score = min(100, array_sum($signalScores));

        $blockThreshold = (int) ($this->config['block_threshold'] ?? 85);
        $challengeThreshold = (int) ($this->config['challenge_threshold'] ?? 65);
        $notifyThreshold = (int) ($this->config['notify_threshold'] ?? 45);

        $level = match (true) {
            $score >= $blockThreshold => LoginRiskLevel::Critical,
            $score >= $challengeThreshold => LoginRiskLevel::High,
            $score >= $notifyThreshold => LoginRiskLevel::Medium,
            default => LoginRiskLevel::Low,
        };

        $trustedAfter = (int) ($this->config['trusted_after_logins'] ?? 3);
        $trustedMaxScore = (int) ($this->config['trusted_max_risk_score'] ?? 25);
        $trustDevice = $knownDevice !== null
            && (int) $knownDevice->login_count >= $trustedAfter
            && $score <= $trustedMaxScore;

        return new LoginRiskAssessmentDto(
            score: $score,
            level: $level,
            signals: $signals,
            signal_scores: $signalScores,
            isNewDevice: $isNewDevice,
            shouldBlock: $score >= $blockThreshold,
            shouldChallenge: $score >= $challengeThreshold,
            shouldNotify: $score >= $notifyThreshold,
            trustDevice: $trustDevice || ($isNewDevice && ! $user->devices()->exists()),
        );
    }

    public function recordLoginAttempt(int $userId, string $ip): void
    {
        $window = (int) (($this->config['rapid_multi_ip']['window_minutes'] ?? 15));
        $key = sprintf('security:login-ips:%d', $userId);
        $ips = Cache::get($key, []);
        if (! is_array($ips)) {
            $ips = [];
        }
        $ips[$ip] = now()->timestamp;
        $cutoff = now()->subMinutes($window)->timestamp;
        $ips = array_filter($ips, static fn (int $ts): bool => $ts >= $cutoff);
        Cache::put($key, $ips, now()->addMinutes($window + 1));
    }

    /**
     * @param  list<string>  $signals
     * @param  array<string, int>  $signalScores
     */
    private function addSignal(array &$signals, array &$signalScores, string $key, int $points): void
    {
        if ($points <= 0) {
            return;
        }
        $signals[] = $key;
        $signalScores[$key] = $points;
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

    /**
     * @return list<string>
     */
    private function knownIpSubnets(User $user): array
    {
        return $user->devices()
            ->pluck('ip')
            ->map(fn (?string $deviceIp): ?string => $this->ipSubnet((string) $deviceIp))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function ipSubnet(string $ip): ?string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);

            return count($parts) === 4 ? implode('.', array_slice($parts, 0, 3)) : null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);

            return implode(':', array_slice($parts, 0, 4));
        }

        return null;
    }

    private function looksLikeBrowser(DeviceContextDto $context): bool
    {
        $ua = strtolower($context->userAgent);

        return str_contains($ua, 'mozilla') || str_contains($ua, 'chrome') || str_contains($ua, 'safari');
    }

    private function failedAttemptsForIp(int $userId, string $ip): int
    {
        return (int) Cache::get(sprintf('security:failed-login:%d:%s', $userId, $ip), 0);
    }

    private function hasRapidMultiIp(int $userId): bool
    {
        $cfg = (array) ($this->config['rapid_multi_ip'] ?? []);
        $minIps = (int) ($cfg['min_distinct_ips'] ?? 3);
        $ips = Cache::get(sprintf('security:login-ips:%d', $userId), []);

        return is_array($ips) && count($ips) >= $minIps;
    }

    private function isUnusualHour(): bool
    {
        $cfg = (array) ($this->config['unusual_hour'] ?? []);
        if (! ($cfg['enabled'] ?? true)) {
            return false;
        }

        $hour = (int) now()->format('G');
        $start = (int) ($cfg['local_hour_start'] ?? 1);
        $end = (int) ($cfg['local_hour_end'] ?? 5);

        return $hour >= $start && $hour < $end;
    }

    private function isDatacenterIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        $prefixes = (array) config('login_security.datacenter_prefixes', []);

        foreach ($prefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($ip, (string) $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isLongAbsence(User $user): bool
    {
        $days = (int) ($this->config['long_absence_days'] ?? 90);
        $last = $user->devices()->max('last_seen_at');

        if ($last === null) {
            return false;
        }

        return now()->parse($last)->lt(now()->subDays($days));
    }
}
