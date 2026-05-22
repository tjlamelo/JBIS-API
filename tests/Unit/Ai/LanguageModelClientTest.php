<?php

declare(strict_types=1);

namespace Tests\Unit\Ai;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Services\FakeLanguageModelClient;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class LanguageModelClientTest extends TestCase
{
    public function test_fake_driver_returns_configured_text(): void
    {
        Config::set('ai.driver', 'fake');
        Config::set('ai.fake.response', 'Hello from fake');

        $client = $this->app->make(LanguageModelClientInterface::class);
        $result = $client->generateContent(new GenerateContentRequest([
            new ChatMessage(ChatRole::User, 'ping'),
        ]));

        self::assertSame('Hello from fake', $result->text);
        self::assertInstanceOf(FakeLanguageModelClient::class, $client);
    }
}
