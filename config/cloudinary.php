<?php

declare(strict_types=1);

$cloudinaryUrl = (string) env('CLOUDINARY_URL', '');
$parsed = $cloudinaryUrl !== '' ? parse_url($cloudinaryUrl) : [];

$cloudName = isset($parsed['host']) ? (string) $parsed['host'] : (string) env('CLOUDINARY_CLOUD_NAME', '');
$apiKey = isset($parsed['user']) ? (string) $parsed['user'] : (string) env('CLOUDINARY_API_KEY', env('CLOUDINARY_KEY', ''));
$apiSecret = isset($parsed['pass']) ? (string) $parsed['pass'] : (string) env('CLOUDINARY_API_SECRET', env('CLOUDINARY_SECRET', ''));

return [
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    // CloudinaryLabs\CloudinaryLaravel config shape.
    'cloud_url' => $cloudinaryUrl !== ''
        ? $cloudinaryUrl
        : ('cloudinary://'.env('CLOUDINARY_KEY').':'.env('CLOUDINARY_SECRET').'@'.env('CLOUDINARY_CLOUD_NAME')),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),

    // Extra compatibility for Cloudinary SDKs expecting a parsed config array.
    'cloud' => [
        'cloud_name' => $cloudName,
        'api_key' => $apiKey,
        'api_secret' => $apiSecret,
        'secure' => true,
    ],
];
