<?php

return [
    'provider' => env('SMS_PROVIDER', 'queen_sms'),

    'providers' => [
        'queen_sms' => \App\Core\Domain\Communication\Services\QueenSmsService::class,
    ],
];
