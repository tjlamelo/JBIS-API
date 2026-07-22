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
 * Extraction texte (PDF natif / OCR) avec prompt et schéma adaptés au type de document.
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
                ."Ne te limite pas à l'identité. Si une entreprise ou un diplôme apparaît dans le texte, il DOIT figurer dans le tableau correspondant.\n"
                ."Interdit de mettre le parcours uniquement dans `notes`.\n\n"
                .$documentText
            : $documentText;

        return $this->generateDraft($profile['system'], $userPrompt, $profile['schema']);
    }

    /**
     * 2e passe CV : force le remplissage des tableaux parcours (quand la 1re passe n'a fait que l'identité).
     *
     * @return array<string, mixed>
     */
    public function extractCvSectionsOnly(string $documentText): array
    {
        $profile = DocumentExtractionProfileRegistry::resolve('CV');

        $userPrompt = <<<PROMPT
Deuxième passe d'extraction CV — IGNORE l'identité déjà connue.

Concentre-toi UNIQUEMENT sur les tableaux suivants (remplis-les au maximum) :
- educations
- experiences
- internships
- languages
- skills
- certifications
- formations
- interests

Règles :
1. user_profile peut rester minimal (chaînes vides OK).
2. notes : laisse vide sauf ambiguïté réelle.
3. Chaque entreprise, diplôme, langue ou compétence visible dans le texte DOIT apparaître dans le tableau correspondant.
4. Ne résume PAS le parcours dans notes.

TEXTE DU CV :

{$documentText}
PROMPT;

        return $this->generateDraft($profile['system'], $userPrompt, $profile['schema']);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function generateDraft(string $systemPrompt, string $userPrompt, array $schema): array
    {
        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $systemPrompt),
                new ChatMessage(ChatRole::User, $userPrompt),
            ],
            options: new GenerationOptions(
                temperature: 0.1,
                maxOutputTokens: max(512, (int) config('ai.document_extraction.max_output_tokens', 8192)),
                responseMimeType: 'application/json',
                responseSchema: $schema,
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return ProfileBundleDraftNormalizer::normalize(
            LanguageModelJsonDecoder::decodeObject($result->text),
        );
    }
}
