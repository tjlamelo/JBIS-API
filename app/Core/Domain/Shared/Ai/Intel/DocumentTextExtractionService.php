<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;
use App\Core\Domain\Shared\Ai\Support\ProfileBundleDraftNormalizer;

/**
 * Extraction texte (PDF natif) avec prompt et schéma adaptés au type de document.
 */
final class DocumentTextExtractionService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function extractDraft(string $documentTypeCode, string $documentText): array
    {
        $profile = DocumentExtractionProfileRegistry::resolve($documentTypeCode);

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $profile['system']),
                new ChatMessage(ChatRole::User, $documentText),
            ],
            options: new GenerationOptions(
                temperature: 0.1,
                maxOutputTokens: 8192,
                responseMimeType: 'application/json',
                responseSchema: $profile['schema'],
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return ProfileBundleDraftNormalizer::normalize(
            LanguageModelJsonDecoder::decodeObject($result->text),
        );
    }
}
