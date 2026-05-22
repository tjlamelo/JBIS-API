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
        $system = <<<'PROMPT'
Tu es un moteur d'extraction pour un ATS (JBIS). On te fournit le texte brut issu d'un ou plusieurs documents candidat (CV, lettres, scans OCR concaténés).

Tâche :
- Fusionne les informations cohérentes ; en cas de conflit, privilégie les dates les plus récentes ou indique l'ambiguïté dans `notes`.
- Remplis le JSON strictement selon le schéma fourni par l'API (types imposés).
- Utilise des chaînes vides ou null là où l'information est absente (ne invente pas de faits).
- Dates au format ISO YYYY-MM-DD lorsque tu peux les inférer avec confiance, sinon chaîne vide.
- `languages.language_name` : nom naturel ou code ISO ; `proficiency_level` : libellé libre (A1…, notion, courant…).
- `formations` : formations continues / certificats courts si distincts des entrées `certifications` ou `educations`.
PROMPT;

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

        return LanguageModelJsonDecoder::decodeObject($result->text);
    }
}
