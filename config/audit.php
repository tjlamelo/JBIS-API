<?php

use App\Core\Domain\Audit\Models\Audit;
use App\Core\Domain\Audit\Resolvers\ApiUserResolver;

return [

    'enabled' => env('AUDITING_ENABLED', true),

    'implementation' => Audit::class,

    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'sanctum',
            'web',
        ],
        'resolver' => ApiUserResolver::class,
    ],

    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    'strict' => false,

    'exclude' => [],

    'empty_values' => true,
    'allowed_empty_values' => [
        'retrieved',
    ],

    'allowed_array_values' => false,

    'timestamps' => false,

    'threshold' => 0,

    'driver' => 'database',

    'drivers' => [
        'database' => [
            'table' => 'audits',
            'connection' => null,
        ],
    ],

    'queue' => [
        'enable' => false,
        'connection' => 'sync',
        'queue' => 'default',
        'delay' => 0,
    ],

    'console' => false,
];
