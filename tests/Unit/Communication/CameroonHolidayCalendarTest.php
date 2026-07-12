<?php

declare(strict_types=1);

namespace Tests\Unit\Communication;

use App\Core\Domain\Communication\Support\CameroonHolidayCalendar;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CameroonHolidayCalendarTest extends TestCase
{
    #[Test]
    public function it_lists_national_day_and_reunion_observance_on_may_20(): void
    {
        $events = (new CameroonHolidayCalendar)->forDate(
            CarbonImmutable::parse('2026-05-20', 'Africa/Douala')
        );

        $codes = collect($events)->pluck('code')->all();

        $this->assertContains('national_day', $codes);
        $this->assertContains('national_unity_reunion', $codes);
    }

    #[Test]
    public function it_lists_good_friday_from_easter_offset(): void
    {
        $events = (new CameroonHolidayCalendar)->forDate(
            CarbonImmutable::parse('2026-04-03', 'Africa/Douala')
        );

        $this->assertContains('good_friday', collect($events)->pluck('code')->all());
    }
}
