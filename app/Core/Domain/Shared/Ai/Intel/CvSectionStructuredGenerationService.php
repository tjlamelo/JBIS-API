<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;

/**
 * Génère le contenu d'une section de CV selon un format JSON (schéma Gemini) fourni par l'appelant.
 */
final class CvSectionStructuredGenerationService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
    ) {}

    /**
     * @param  array<string, mixed>  $geminiResponseSchema  Schéma racine Gemini (ex. type OBJECT, properties…).
     * @return array<string, mixed>
     */
    public function generate(string $sectionInstruction, string $sourceText, array $geminiResponseSchema, ?float $temperature = 0.25): array
    {
        $system = <<<'PROMPT'
Tu remplis une section de CV pour JBIS. Respecte strictement le schéma JSON imposé par l'API.
Ne fabrique pas d'expérience ou de diplôme non suggéré par le texte source ; utilise des champs vides si besoin.
PROMPT;

        $user = "Consignes spécifiques de la section :\n{$sectionInstruction}\n\nTexte source (brut) :\n{$sourceText}";

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $system),
                new ChatMessage(ChatRole::User, $user),
            ],
            options: new GenerationOptions(
                temperature: $temperature,
                maxOutputTokens: 4096,
                responseMimeType: 'application/json',
                responseSchema: $geminiResponseSchema,
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return LanguageModelJsonDecoder::decodeObject($result->text);
    }
}
