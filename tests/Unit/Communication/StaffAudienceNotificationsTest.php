<?php

declare(strict_types=1);

namespace Tests\Unit\Communication;

use App\Core\Domain\Communication\Actions\NotifyStaffWelcomeAction;
use App\Core\Domain\Identity\Support\ApplicationRole;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StaffAudienceNotificationsTest extends TestCase
{
    #[Test]
    public function notification_keys_exist_for_staff_and_candidate(): void
    {
        foreach (['fr', 'en'] as $locale) {
            foreach (['week_start', 'weekend', 'birthday', 'birthday_followup'] as $key) {
                foreach (['candidate', 'staff'] as $audience) {
                    $title = trans("notifications.{$key}.{$audience}.title", [], $locale);
                    $body = trans(
                        "notifications.{$key}.{$audience}.body",
                        in_array($key, ['birthday', 'birthday_followup'], true) ? ['name' => 'Ada'] : [],
                        $locale,
                    );
                    $this->assertNotSame("notifications.{$key}.{$audience}.title", $title);
                    $this->assertNotSame('', $title);
                    $this->assertNotSame('', $body);
                    if (in_array($key, ['birthday', 'birthday_followup'], true)) {
                        $this->assertStringContainsString('Ada', $body);
                    }
                }
            }

            $welcome = trans('notifications.staff_welcome.title', [], $locale);
            $this->assertNotSame('notifications.staff_welcome.title', $welcome);
        }
    }

    #[Test]
    public function became_staff_detection_only_on_transition(): void
    {
        $action = $this->app->make(NotifyStaffWelcomeAction::class);
        $ref = new \ReflectionClass($action);
        $method = $ref->getMethod('containsStaffRole');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($action, [ApplicationRole::CANDIDATE]));
        $this->assertTrue($method->invoke($action, [ApplicationRole::STAFF]));
        $this->assertTrue($method->invoke($action, [ApplicationRole::ADMIN]));
        $this->assertTrue($method->invoke($action, [ApplicationRole::SUPERADMIN]));
    }
}
