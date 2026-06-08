<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Communication;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Communication\Enums\NewsletterSubscriptionStatus;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Location\Models\Country;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Role::query()->count() === 0) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function public_can_subscribe_and_unsubscribe_with_token(): void
    {
        $email = 'newsletter-'.uniqid().'@example.com';

        $this->postJson('/api/v1/public/newsletter/subscribe', [
            'email' => $email,
            'language' => 'fr',
            'scope' => 'both',
            'source' => 'test',
        ])->assertStatus(201)
            ->assertJsonPath('data.subscription.status', 'subscribed');

        $token = (string) NewsletterSubscription::query()->where('email', $email)->value('unsubscribe_token');

        $this->postJson('/api/v1/public/newsletter/unsubscribe', ['token' => $token])
            ->assertOk()
            ->assertJsonPath('data.subscription.status', 'unsubscribed');
    }

    #[Test]
    public function newsletter_command_sends_mail_in_preferred_language(): void
    {
        Mail::fake();

        $cameroon = Country::query()->firstOrCreate(
            ['code' => 'CM'],
            ['name' => json_encode(['fr' => 'Cameroun']), 'phone_code' => '+237'],
        );

        Offer::factory()->create([
            'status' => OfferStatus::Published,
            'country_id' => $cameroon->id,
            'published_at' => now(),
            'expiration_date' => now()->addMonth(),
        ]);

        $email = 'nl-send-'.uniqid().'@example.com';
        NewsletterSubscription::query()->create([
            'email' => $email,
            'language' => 'en',
            'scope' => 'national',
            'status' => NewsletterSubscriptionStatus::Subscribed,
            'unsubscribe_token' => 'token-'.uniqid(),
            'subscribed_at' => now(),
        ]);

        $this->artisan('newsletter:send-offers')
            ->assertExitCode(0);

        Mail::assertSent(\App\Core\Application\Mail\Mailable\OfferNewsletterMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email) && $mail->language === 'en';
        });
    }

    #[Test]
    public function admin_can_trigger_newsletter_dispatch(): void
    {
        Mail::fake();

        $admin = \App\Core\Domain\Identity\Models\User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);

        NewsletterSubscription::query()->create([
            'email' => 'admin-nl-'.uniqid().'@example.com',
            'language' => 'fr',
            'scope' => 'both',
            'status' => NewsletterSubscriptionStatus::Subscribed,
            'unsubscribe_token' => 'token-'.uniqid(),
            'subscribed_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/mail-campaigns/newsletter/offers')
            ->assertOk()
            ->assertJsonStructure(['data' => ['stats']]);
    }
}
