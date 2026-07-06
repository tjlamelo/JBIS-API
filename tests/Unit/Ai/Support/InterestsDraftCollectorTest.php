<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Support;

use App\Core\Domain\Shared\Ai\Support\InterestsDraftCollector;
use PHPUnit\Framework\TestCase;

final class InterestsDraftCollectorTest extends TestCase
{
    public function test_collects_from_alternate_root_keys_and_formats(): void
    {
        $items = InterestsDraftCollector::collect([
            'loisirs' => 'Football, Lecture',
            'hobbies' => [
                ['title' => 'Bénévolat'],
                'Football',
            ],
            'interests' => [
                ['name' => 'Musique'],
            ],
        ]);

        self::assertCount(4, $items);
        $names = array_column($items, 'name');
        self::assertContains('Football', $names);
        self::assertContains('Lecture', $names);
        self::assertContains('Bénévolat', $names);
        self::assertContains('Musique', $names);
    }
}
