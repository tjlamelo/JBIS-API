<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Identity;

use App\Core\Application\Mail\Jobs\SendAdminCreatedAccountMailJob;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class AdminCreatedAccountFlowTest extends TestCase
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
    public function admin_create_with_email_sends_credentials_and_requires_password_change(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole(ApplicationRole::ADMIN);
        Sanctum::actingAs($admin);

        $email = 'candidat-'.uniqid().'@example.com';

        $create = $this->postJson('/api/v1/identity/admin/users', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => $email,
            'roles' => [ApplicationRole::CANDIDATE],
            'send_account_email' => true,
        ])->assertCreated();

        $create->assertJsonPath('data.account_notice.account_email_sent', true);

        $candidate = User::query()->where('email', $email)->first();
        $this->assertNotNull($candidate);
        $this->assertTrue($candidate->must_change_password);

        Queue::assertPushed(SendAdminCreatedAccountMailJob::class, function (SendAdminCreatedAccountMailJob $job) use ($candidate): bool {
            return $job->userId === $candidate->id;
        });

        $login = $this->postJson('/api/v1/login', [
            'login' => $email,
            'password' => (string) config('identity.default_user_password'),
        ])->assertOk();

        $login->assertJsonPath('data.user.must_change_password', true);
        $token = (string) $login->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/dashboard')
            ->assertForbidden()
            ->assertJsonPath('data.must_change_password', true);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/me/required-password-change', [
                'current_password' => (string) config('identity.default_user_password'),
                'password' => 'NewSecurePass1!',
                'password_confirmation' => 'NewSecurePass1!',
            ])
            ->assertOk()
            ->assertJsonPath('data.must_relogin', true);

        $candidate->refresh();
        $this->assertFalse($candidate->must_change_password);

        $this->postJson('/api/v1/login', [
            'login' => $email,
            'password' => 'NewSecurePass1!',
        ])->assertOk();
    }
}
