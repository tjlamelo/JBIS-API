<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Services;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentResult;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelConfigurationException;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelTransportException;
use Illuminate\Support\Facades\Http;

final class GeminiLanguageModelClient implements LanguageModelClientInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function generateContent(GenerateContentRequest $request): GenerateContentResult
    {
        if ($this->apiKey === '') {
            throw new LanguageModelConfigurationException(
                'Clé API Gemini manquante : définissez AI_GEMINI_API_KEY dans l\'environnement.'
            );
        }

        $payload = $this->buildPayload($request);
        $url = sprintf(
            '%s/models/%s:generateContent',
            $this->baseUrl,
            rawurlencode($this->model)
        );

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withQueryParameters(['key' => $this->apiKey])
            ->post($url, $payload);

        if (! $response->successful()) {
            $message = $this->formatHttpError($response->json(), $response->status());
            throw new LanguageModelTransportException($message);
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        return $this->parseGenerateContentResponse($body);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(GenerateContentRequest $request): array
    {
        $systemParts = [];
        $contents = [];

        foreach ($request->messages as $message) {
            if ($message->role === ChatRole::System) {
                $systemParts[] = $message->content;

                continue;
            }

            $geminiRole = $message->role === ChatRole::Assistant ? 'model' : 'user';
            $parts = [['text' => $message->content]];

            foreach ($message->allImageUrls() as $imageUrl) {
                $parts[] = $this->buildImagePart($imageUrl);
            }

            $contents[] = [
                'role' => $geminiRole,
                'parts' => $parts,
            ];
        }

        $payload = ['contents' => $contents];

        if ($systemParts !== []) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => implode("\n\n", $systemParts)]],
            ];
        }

        $options = $request->options;
        if ($options !== null) {
            $generationConfig = [];
            if ($options->temperature !== null) {
                $generationConfig['temperature'] = $options->temperature;
            }
            if ($options->maxOutputTokens !== null) {
                $generationConfig['maxOutputTokens'] = $options->maxOutputTokens;
            }
            if ($options->responseMimeType !== null) {
                $generationConfig['responseMimeType'] = $options->responseMimeType;
            }
            if ($options->responseSchema !== null && $options->responseSchema !== []) {
                $generationConfig['responseSchema'] = $options->responseSchema;
            }
            if ($generationConfig !== []) {
                $payload['generationConfig'] = $generationConfig;
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildImagePart(string $imageUrl): array
    {
        if (str_starts_with($imageUrl, 'data:')) {
            if (! preg_match('#^data:([^;]+);base64,(.+)$#', $imageUrl, $matches)) {
                throw new LanguageModelTransportException('Data URL image invalide pour Gemini.');
            }

            return [
                'inline_data' => [
                    'mime_type' => $matches[1],
                    'data' => $matches[2],
                ],
            ];
        }

        return [
            'file_data' => [
                'file_uri' => $imageUrl,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function parseGenerateContentResponse(array $body): GenerateContentResult
    {
        /** @var list<array<string, mixed>>|null $candidates */
        $candidates = isset($body['candidates']) && is_array($body['candidates']) ? $body['candidates'] : null;

        if ($candidates === null || $candidates === []) {
            $blockReason = null;
            if (isset($body['promptFeedback']['blockReason']) && is_string($body['promptFeedback']['blockReason'])) {
                $blockReason = $body['promptFeedback']['blockReason'];
            }
            $detail = $blockReason !== null
                ? sprintf('Prompt bloqué (%s).', $blockReason)
                : 'Réponse vide du fournisseur Gemini.';

            throw new LanguageModelTransportException($detail);
        }

        $first = $candidates[0];
        $finishReason = isset($first['finishReason']) && is_string($first['finishReason'])
            ? $first['finishReason']
            : null;

        $text = $this->extractTextFromCandidate($first);
        if ($text === '') {
            throw new LanguageModelTransportException(
                'Le modèle n\'a renvoyé aucun texte exploitable (candidat sans parts texte).'
            );
        }

        return new GenerateContentResult(
            text: $text,
            finishReason: $finishReason,
            raw: $body,
        );
    }

    /**
     * @param  array<string, mixed>  $candidate
     */
    private function extractTextFromCandidate(array $candidate): string
    {
        $content = $candidate['content'] ?? null;
        if (! is_array($content)) {
            return '';
        }

        $parts = $content['parts'] ?? null;
        if (! is_array($parts)) {
            return '';
        }

        $chunks = [];
        foreach ($parts as $part) {
            if (! is_array($part)) {
                continue;
            }
            if (isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return implode('', $chunks);
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function formatHttpError(?array $json, int $status): string
    {
        if (isset($json['error']['message']) && is_string($json['error']['message'])) {
            return sprintf('Gemini HTTP %d : %s', $status, $json['error']['message']);
        }

        return sprintf('Gemini HTTP %d : erreur sans message détaillé.', $status);
    }
}
