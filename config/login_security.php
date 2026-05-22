<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Scoring dynamique (style Google) — poids ajustables sans redeploy code
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'new_device' => 55,
        'new_ip_subnet' => 25,
        'ip_changed_on_known_device' => 20,
        'user_agent_changed' => 15,
        'browser_headers_missing' => 10,
        'failed_attempts_elevated' => 20,
        'failed_attempts_critical' => 40,
        'rapid_multi_ip' => 35,
        'unusual_hour' => 10,
        'datacenter_ip' => 25,
        'long_absence_new_device' => 15,
    ],

    /** Score >= notify : e-mail + device non approuve */
    'notify_threshold' => 45,

    /** Score >= challenge : exiger 2FA si active, sinon connexion marquee a risque */
    'challenge_threshold' => 65,

    /** Score >= block : connexion refusee */
    'block_threshold' => 85,

    'failed_attempts' => [
        'elevated' => 3,
        'critical' => 8,
    ],

    'rapid_multi_ip' => [
        'window_minutes' => 15,
        'min_distinct_ips' => 3,
    ],

    'unusual_hour' => [
        'enabled' => true,
        'local_hour_start' => 1,
        'local_hour_end' => 5,
    ],

    'long_absence_days' => 90,

    'alert_cooldown_hours' => 12,

    'trusted_after_logins' => 3,

    'trusted_max_risk_score' => 25,
];
