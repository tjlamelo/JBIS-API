<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Mail;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CpanelMailboxControllerTest extends TestCase
{
    #[Test]
    public function it_creates_a_mailbox_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/add_pop*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/cpanel/mailboxes', [
            'local_part' => 'support',
            'password' => 'PasswordStrong123',
            'quota_mb' => 1024,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_lists_mailboxes_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/list_pops*' => Http::response([
                'status' => 1,
                'data' => [
                    [
                        'email' => 'support@jbis.cm',
                        'login' => 'support',
                        'domain' => 'jbis.cm',
                        'suspended_login' => 0,
                        'diskused' => '0',
                        'diskquota' => '1024',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/cpanel/mailboxes');

        $response->assertStatus(200)
            ->assertJsonPath('data.mailboxes.0.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_deletes_a_mailbox_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/delete_pop*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->deleteJson('/api/v1/cpanel/mailboxes/support');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_updates_mailbox_password_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/passwd_pop*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->putJson('/api/v1/cpanel/mailboxes/support/password', [
            'password' => 'NewStrongPassword123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_suspends_a_mailbox_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/suspend_login*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/cpanel/mailboxes/support/suspend');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_updates_mailbox_quota_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/edit_pop_quota*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->putJson('/api/v1/cpanel/mailboxes/support/quota', [
            'quota_mb' => 2048,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }

    #[Test]
    public function it_unsuspends_a_mailbox_via_cpanel_endpoint(): void
    {
        $this->withoutMiddleware();

        config()->set('services.cpanel', [
            'host' => 'light.o2switch.net',
            'username' => 'cpanel-user',
            'token' => 'cpanel-token',
            'primary_domain' => 'jbis.cm',
            'timeout' => 10,
        ]);

        Http::fake([
            'https://light.o2switch.net:2083/execute/Email/unsuspend_login*' => Http::response([
                'status' => 1,
                'errors' => null,
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/cpanel/mailboxes/support/unsuspend');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'support@jbis.cm');
    }
}
