<?php

declare(strict_types=1);

namespace Tests\Unit\Ai;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Services\GroqLanguageModelClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GroqLanguageModelClientTest extends TestCase
{
    public function test_groq_driver_returns_assistant_text(): void
    {
        Config::set('ai.driver', 'groq');
        Config::set('ai.groq.api_key', 'test-key');
        Config::set('ai.groq.model', 'llama-3.3-70b-versatile');
        Config::set('ai.groq.vision_model', 'qwen/qwen3.6-27b');
        Config::set('ai.groq.base_url', 'https://api.groq.com/openai/v1');
        Config::set('ai.groq.timeout', 30);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'finish_reason' => 'stop',
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Hello from Groq',
                        ],
                    ],
                ],
            ]),
        ]);

        $client = $this->app->make(LanguageModelClientInterface::class);
        $result = $client->generateContent(new GenerateContentRequest([
            new ChatMessage(ChatRole::User, 'ping'),
        ]));

        self::assertSame('Hello from Groq', $result->text);
        self::assertInstanceOf(GroqLanguageModelClient::class, $client);

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
                && ($body['model'] ?? null) === 'llama-3.3-70b-versatile'
                && ($body['messages'][0]['content'] ?? null) === 'ping';
        });
    }

    public function test_vision_requests_use_json_object_not_json_schema(): void
    {
        Config::set('ai.groq.api_key', 'test-key');
        Config::set('ai.groq.model', 'llama-3.3-70b-versatile');
        Config::set('ai.groq.vision_model', 'qwen/qwen3.6-27b');
        Config::set('ai.groq.base_url', 'https://api.groq.com/openai/v1');
        Config::set('ai.groq.timeout', 30);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'finish_reason' => 'stop',
                        'message' => [
                            'role' => 'assistant',
                            'content' => '{"notes":"","user_profile":{}}',
                        ],
                    ],
                ],
            ]),
        ]);

        $client = new GroqLanguageModelClient(
            apiKey: 'test-key',
            model: 'llama-3.3-70b-versatile',
            visionModel: 'qwen/qwen3.6-27b',
            baseUrl: 'https://api.groq.com/openai/v1',
            timeout: 30,
        );

        $client->generateContent(new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::User, 'Analyse', imageUrls: ['data:image/png;base64,abc']),
            ],
            options: new \App\Core\Domain\Shared\Ai\DTOs\GenerationOptions(
                responseMimeType: 'application/json',
                responseSchema: ['type' => 'OBJECT', 'properties' => ['notes' => ['type' => 'STRING']]],
            ),
        ));

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return ($body['model'] ?? null) === 'qwen/qwen3.6-27b'
                && ($body['response_format']['type'] ?? null) === 'json_object'
                && ! isset($body['response_format']['json_schema']);
        });
    }

    public function test_text_model_uses_json_object_for_llama_3_3(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'finish_reason' => 'stop',
                        'message' => ['role' => 'assistant', 'content' => '{}'],
                    ],
                ],
            ]),
        ]);

        $client = new GroqLanguageModelClient(
            apiKey: 'test-key',
            model: 'llama-3.3-70b-versatile',
            visionModel: 'qwen/qwen3.6-27b',
            baseUrl: 'https://api.groq.com/openai/v1',
            timeout: 30,
        );

        $client->generateContent(new GenerateContentRequest(
            messages: [new ChatMessage(ChatRole::User, 'texte cv')],
            options: new \App\Core\Domain\Shared\Ai\DTOs\GenerationOptions(
                responseMimeType: 'application/json',
                responseSchema: ['type' => 'OBJECT', 'properties' => ['notes' => ['type' => 'STRING']]],
            ),
        ));

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return ($body['model'] ?? null) === 'llama-3.3-70b-versatile'
                && ($body['response_format']['type'] ?? null) === 'json_object';
        });
    }
}
