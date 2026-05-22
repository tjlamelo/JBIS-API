<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Services;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentResult;

/**
 * Implémentation sans appel réseau (tests, CI, environnements sans clé API).
 */
final class FakeLanguageModelClient implements LanguageModelClientInterface
{
    /**
     * @param  array<string, mixed>|null  $structuredJsonStub  Réponse JSON simulée quand `GenerationOptions::wantsStructuredJson()` est vrai.
     */
    public function __construct(
        private readonly string $responseText,
        private readonly ?array $structuredJsonStub = null,
    ) {}

    public function generateContent(GenerateContentRequest $request): GenerateContentResult
    {
        $options = $request->options;
        if ($options !== null && $options->wantsStructuredJson()) {
            $stub = $this->structuredJsonStub ?? ['_fake' => true, 'note' => 'Stub JSON non configuré (ai.fake.structured_stub).'];

            return new GenerateContentResult(
                text: json_encode($stub, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                finishReason: 'STOP',
                raw: ['driver' => 'fake', 'structured' => true],
            );
        }

        return new GenerateContentResult(
            text: $this->responseText,
            finishReason: 'STOP',
            raw: ['driver' => 'fake'],
        );
    }
}
