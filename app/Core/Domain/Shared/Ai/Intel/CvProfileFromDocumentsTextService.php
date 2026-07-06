<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\ProfileBundleGeminiSchema;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;
use App\Core\Domain\Shared\Ai\Support\ProfileBundleDraftNormalizer;

/**
 * Extrait un brouillon structuré (profil, formations, expériences, etc.) à partir de texte issu de documents utilisateur.
 *
 * La persistance en base (après validation humaine) reste du ressort de la couche application / actions métier.
 */
final class CvProfileFromDocumentsTextService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function extractDraft(string $aggregatedDocumentsText): array
    {
        $system = CvExtractionSystemPrompt::text();

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $system),
                new ChatMessage(ChatRole::User, $aggregatedDocumentsText),
            ],
            options: new GenerationOptions(
                temperature: 0.15,
                maxOutputTokens: 8192,
                responseMimeType: 'application/json',
                responseSchema: ProfileBundleGeminiSchema::responseSchema(),
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return ProfileBundleDraftNormalizer::normalize(
            LanguageModelJsonDecoder::decodeObject($result->text),
        );
    }
}
