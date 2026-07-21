<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Services;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentResult;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelConfigurationException;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelTransportException;
use App\Core\Domain\Shared\Ai\Support\GeminiSchemaAdapter;
use App\Core\Domain\Shared\Ai\Support\LanguageModelHttp;
use Illuminate\Support\Facades\Log;

/**
 * Client Groq Cloud (API OpenAI-compatible : POST /openai/v1/chat/completions).
 *
 * @see https://console.groq.com/docs/
 */
final class GroqLanguageModelClient implements LanguageModelClientInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $visionModel,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function generateContent(GenerateContentRequest $request): GenerateContentResult
    {
        if ($this->apiKey === '') {
            throw new LanguageModelConfigurationException(
                'Clé API Groq manquante : définissez AI_GROQ_API_KEY dans l\'environnement.'
            );
        }

        $payload = $this->buildPayload($request);
        $url = rtrim($this->baseUrl, '/').'/chat/completions';
        $usesVision = $this->requestUsesVision($request);
        $model = $usesVision ? $this->visionModel : $this->model;

        Log::info('[groq] Requête chat/completions', [
            'model' => $model,
            'vision' => $usesVision,
            'messages_count' => count($request->messages),
            'structured_json' => $request->options?->wantsStructuredJson() ?? false,
            'response_format' => $payload['response_format']['type'] ?? null,
        ]);

        $response = $this->postChatCompletion($url, $payload);

        if (! $response->successful() && $this->shouldFallbackToJsonObject($response, $payload)) {
            Log::info('[groq] Repli response_format json_object', ['model' => $model]);
            $payload = $this->withJsonObjectResponseFormat($payload);
            $response = $this->postChatCompletion($url, $payload);
        }

        if (! $response->successful()) {
            LanguageModelHttp::throwIfRateLimited($response, 'groq');
            $message = $this->formatHttpError($response->json(), $response->status());
            Log::warning('[groq] Erreur HTTP chat/completions', [
                'status' => $response->status(),
                'model' => $model,
                'message' => $message,
            ]);
            throw new LanguageModelTransportException($message);
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        return $this->parseChatCompletionResponse($body);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(GenerateContentRequest $request): array
    {
        $messages = [];
        $usesVision = false;

        foreach ($request->messages as $message) {
            $role = match ($message->role) {
                ChatRole::System => 'system',
                ChatRole::Assistant => 'assistant',
                ChatRole::User => 'user',
            };

            $imageUrls = $message->allImageUrls();
            if ($imageUrls !== []) {
                $usesVision = true;
                $content = [['type' => 'text', 'text' => $message->content]];
                foreach ($imageUrls as $imageUrl) {
                    $content[] = ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]];
                }
                $messages[] = [
                    'role' => $role,
                    'content' => $content,
                ];

                continue;
            }

            $messages[] = [
                'role' => $role,
                'content' => $message->content,
            ];
        }

        $payload = [
            'model' => $usesVision ? $this->visionModel : $this->model,
            'messages' => $messages,
            'stream' => false,
        ];

        $options = $request->options;
        if ($options === null) {
            return $payload;
        }

        if ($options->temperature !== null) {
            $payload['temperature'] = $options->temperature;
        }

        if ($options->maxOutputTokens !== null) {
            $payload['max_tokens'] = $options->maxOutputTokens;
        }

        if ($options->wantsStructuredJson()) {
            $payload['response_format'] = $this->resolveResponseFormat(
                $usesVision,
                $options->responseSchema,
                (string) $payload['model'],
            );
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>|null  $responseSchema
     * @return array<string, mixed>
     */
    private function resolveResponseFormat(bool $usesVision, ?array $responseSchema, string $model): array
    {
        if ($usesVision || ! $this->modelSupportsJsonSchema($model)) {
            return ['type' => 'json_object'];
        }

        if ($responseSchema !== null && $responseSchema !== []) {
            return [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'structured_response',
                    'schema' => GeminiSchemaAdapter::toJsonSchema($responseSchema),
                    'strict' => false,
                ],
            ];
        }

        return ['type' => 'json_object'];
    }

    private function modelSupportsJsonSchema(string $model): bool
    {
        return in_array($model, [
            'openai/gpt-oss-20b',
            'openai/gpt-oss-120b',
            'openai/gpt-oss-safeguard-20b',
            'meta-llama/llama-4-scout-17b-16e-instruct',
            'qwen/qwen3.6-27b',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withJsonObjectResponseFormat(array $payload): array
    {
        $payload['response_format'] = ['type' => 'json_object'];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function shouldFallbackToJsonObject(\Illuminate\Http\Client\Response $response, array $payload): bool
    {
        if (($payload['response_format']['type'] ?? null) !== 'json_schema') {
            return false;
        }

        $message = $this->formatHttpError($response->json(), $response->status());

        return str_contains(strtolower($message), 'json_schema')
            || str_contains(strtolower($message), 'response format');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postChatCompletion(string $url, array $payload): \Illuminate\Http\Client\Response
    {
        return LanguageModelHttp::postJson(
            url: $url,
            payload: $payload,
            provider: 'groq',
            timeout: $this->timeout,
            bearerToken: $this->apiKey,
        );
    }

    private function requestUsesVision(GenerateContentRequest $request): bool
    {
        foreach ($request->messages as $message) {
            if ($message->allImageUrls() !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function parseChatCompletionResponse(array $body): GenerateContentResult
    {
        /** @var list<array<string, mixed>>|null $choices */
        $choices = isset($body['choices']) && is_array($body['choices']) ? $body['choices'] : null;

        if ($choices === null || $choices === []) {
            throw new LanguageModelTransportException('Réponse vide du fournisseur Groq (choices absent).');
        }

        $first = $choices[0];
        $finishReason = isset($first['finish_reason']) && is_string($first['finish_reason'])
            ? $first['finish_reason']
            : null;

        $message = $first['message'] ?? null;
        if (! is_array($message)) {
            throw new LanguageModelTransportException('Réponse Groq invalide : message absent.');
        }

        $text = isset($message['content']) && is_string($message['content']) ? $message['content'] : '';
        if ($text === '') {
            throw new LanguageModelTransportException(
                'Le modèle Groq n\'a renvoyé aucun texte exploitable.'
            );
        }

        return new GenerateContentResult(
            text: $text,
            finishReason: $finishReason,
            raw: $body,
        );
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function formatHttpError(?array $json, int $status): string
    {
        if (isset($json['error']['message']) && is_string($json['error']['message'])) {
            return sprintf('Groq HTTP %d : %s', $status, $json['error']['message']);
        }

        if (isset($json['message']) && is_string($json['message'])) {
            return sprintf('Groq HTTP %d : %s', $status, $json['message']);
        }

        return sprintf('Groq HTTP %d : erreur sans message détaillé.', $status);
    }
}
