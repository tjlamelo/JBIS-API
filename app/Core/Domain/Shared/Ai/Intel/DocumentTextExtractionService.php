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

        $userPrompt = $documentTypeCode === 'CV'
            ? "Texte extrait du CV (toutes les pages fournies).\n\n"
                ."Extrais TOUTES les sections dans le JSON : identité, formations, expériences, stages, langues, compétences, certifications, centres d'intérêt.\n"
                ."Ne te limite pas à l'identité. Si une entreprise ou un diplôme apparaît dans le texte, il DOIT figurer dans le tableau correspondant.\n\n"
                .$documentText
            : $documentText;

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $profile['system']),
                new ChatMessage(ChatRole::User, $userPrompt),
            ],
            options: new GenerationOptions(
                temperature: 0.1,
                maxOutputTokens: max(512, (int) config('ai.document_extraction.max_output_tokens', 4096)),
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
