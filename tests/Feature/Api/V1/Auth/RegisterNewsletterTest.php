<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Identity\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class RegisterNewsletterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('permissions') && Role::query()->count() === 0) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function register_with_newsletter_creates_subscription(): void
    {
        $email = 'register-nl-'.uniqid().'@example.com';

        $this->postJson('/api/v1/register', [
            'name' => 'Ada Lovelace',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'newsletter' => true,
            'newsletter_scope' => 'both',
        ])->assertCreated();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => $email,
            'user_id' => $user->id,
            'status' => NewsletterSubscriptionStatus::Subscribed->value,
            'source' => 'registration',
        ]);

        $user->load('settings');
        $this->assertTrue((bool) ($user->settings?->marketing['newsletter'] ?? false));
    }

    #[Test]
    public function register_without_newsletter_skips_subscription(): void
    {
        $email = 'register-no-nl-'.uniqid().'@example.com';

        $this->postJson('/api/v1/register', [
            'name' => 'Charles Babbage',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'newsletter' => false,
        ])->assertCreated();

        $this->assertFalse(
            NewsletterSubscription::query()->where('email', $email)->exists(),
        );
    }
}
