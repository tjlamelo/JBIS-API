<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'timezone',
        'notifications',
        'privacy',
        'marketing',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'notifications' => 'array',
        'privacy' => 'array',
        'marketing' => 'array',
    ];

    protected $attributes = [
        'language' => 'fr',
        'theme' => 'system',
        'timezone' => 'Africa/Douala',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultNotifications(): array
    {
        return [
            'email' => [
                'applications' => true,
                'messages' => true,
                'marketing' => false,
            ],
            'push' => [
                'applications' => true,
                'messages' => true,
            ],
            'sms' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultPrivacy(): array
    {
        return [
            'profile_visible_to_partners' => false,
            'show_online_status' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultMarketing(): array
    {
        return [
            'newsletter' => false,
            'newsletter_scope' => 'both',
            'partner_offers' => false,
        ];
    }
}
