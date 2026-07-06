<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Support;

use App\Core\Domain\Shared\Ai\Support\ProfileBundleDraftNormalizer;
use Tests\TestCase;

final class ProfileBundleDraftNormalizerTest extends TestCase
{
    public function test_maps_flat_cv_fields_to_user_profile(): void
    {
        $normalized = ProfileBundleDraftNormalizer::normalize([
            'first_name' => 'Joel',
            'last_name' => 'Dupont',
            'email' => 'joel@example.com',
            'phone' => '+237600000000',
            'summary' => 'Développeur',
            'experiences' => [['job_title' => 'Dev', 'company_name' => 'ACME']],
        ]);

        self::assertSame('Joel', $normalized['user_profile']['first_name'] ?? null);
        self::assertSame('Dupont', $normalized['user_profile']['last_name'] ?? null);
        self::assertSame('joel@example.com', $normalized['user_profile']['email_institutional'] ?? null);
        self::assertSame('Développeur', $normalized['user_profile']['bio'] ?? null);
        self::assertCount(1, $normalized['experiences'] ?? []);
    }
}
