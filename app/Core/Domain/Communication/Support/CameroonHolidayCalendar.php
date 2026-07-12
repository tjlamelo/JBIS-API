<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Support;

use Carbon\CarbonImmutable;

final class CameroonHolidayCalendar
{
    /**
     * @return list<array{code: string, title: array<string, string>|string, body: array<string, string>|string, date: string}>
     */
    public function forDate(CarbonImmutable|string $date): array
    {
        $day = $date instanceof CarbonImmutable
            ? $date->timezone((string) config('cameroon_holidays.timezone', 'Africa/Douala'))
            : CarbonImmutable::parse($date, (string) config('cameroon_holidays.timezone', 'Africa/Douala'));

        $mmdd = $day->format('m-d');
        $ymd = $day->toDateString();
        $events = [];

        foreach (['fixed', 'observances'] as $group) {
            $entry = config("cameroon_holidays.{$group}.{$mmdd}");
            if (is_array($entry) && isset($entry['code'], $entry['title'], $entry['body'])) {
                $events[] = $this->mapEntry($entry, $ymd);
            }
        }

        $easter = $this->gregorianEasterSunday($day->year);

        foreach ((array) config('cameroon_holidays.easter_offset', []) as $offset => $entry) {
            if (! is_array($entry) || ! isset($entry['code'], $entry['title'], $entry['body'])) {
                continue;
            }
            if ($easter->addDays((int) $offset)->toDateString() === $ymd) {
                $events[] = $this->mapEntry($entry, $ymd);
            }
        }

        $islamic = (array) config("cameroon_holidays.islamic_by_year.{$day->year}", []);
        foreach ($islamic as $entry) {
            if (! is_array($entry) || ($entry['date'] ?? null) !== $ymd) {
                continue;
            }
            $events[] = $this->mapEntry($entry, $ymd);
        }

        $unique = [];
        foreach ($events as $event) {
            $unique[$event['code']] = $event;
        }

        return array_values($unique);
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array{code: string, title: array<string, string>|string, body: array<string, string>|string, date: string}
     */
    private function mapEntry(array $entry, string $ymd): array
    {
        return [
            'code' => (string) $entry['code'],
            'title' => $entry['title'],
            'body' => $entry['body'],
            'date' => isset($entry['date']) ? (string) $entry['date'] : $ymd,
        ];
    }

    private function gregorianEasterSunday(int $year): CarbonImmutable
    {
        $tz = (string) config('cameroon_holidays.timezone', 'Africa/Douala');
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $dayOfMonth = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::create($year, $month, $dayOfMonth, 0, 0, 0, $tz)->startOfDay();
    }
}
