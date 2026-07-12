<?php

declare(strict_types=1);

namespace Tests\Unit\Ai;

use App\Core\Domain\Catalog\Support\OfferPublicationScheduler;
use App\Core\Domain\Shared\Ai\Support\AiScalarText;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AiScalarTextAndOfferPublicationTest extends TestCase
{
    #[Test]
    public function it_flattens_array_responsibilities_without_array_to_string_error(): void
    {
        $text = AiScalarText::from([
            'Gérer le planning',
            ['text' => 'Suivre les KPI'],
            ['name' => 'Former l’équipe'],
        ]);

        $this->assertSame("Gérer le planning\nSuivre les KPI\nFormer l’équipe", $text);
        $this->assertSame('Jean Dupont', AiScalarText::from(['Jean', 'Dupont'], ' '));
        $this->assertSame('', AiScalarText::from(null));
    }

    #[Test]
    public function it_keeps_future_publication_as_draft(): void
    {
        $now = Carbon::parse('2026-07-12 10:00:00');
        $result = OfferPublicationScheduler::normalize([
            'status' => 'PUBLISHED',
            'published_at' => '2026-07-15 09:00:00',
        ], $now);

        $this->assertSame('DRAFT', $result['status']);
        $this->assertSame('2026-07-15 09:00:00', $result['published_at']);
    }

    #[Test]
    public function it_publishes_immediately_when_scheduled_date_is_past(): void
    {
        $now = Carbon::parse('2026-07-12 10:00:00');
        $result = OfferPublicationScheduler::normalize([
            'status' => 'DRAFT',
            'published_at' => '2026-07-11 09:00:00',
        ], $now);

        $this->assertSame('PUBLISHED', $result['status']);
        $this->assertSame('2026-07-11 09:00:00', $result['published_at']);
    }
}
