<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\OfferFromPlainTextGeminiSchema;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;

/**
 * Transforme une offre rédigée en texte libre en champs structurés (alignés sur le modèle Offer + champs enrichis).
 *
 * Les champs `expectations` et `specifications` servent à matérialiser des contenus souvent implicites dans le texte ;
 * vous pouvez les mapper vers `meta`, `specific_documents`, ou de futures colonnes lors de la persistance.
 */
final class JobOfferFromPlainTextService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function structureDraft(string $rawOfferText): array
    {
        $system = <<<'PROMPT'
Tu es un assistant de saisie d'offres d'emploi pour JBIS. Le message utilisateur contient une offre en texte brut (email, PDF texte, annonce copiée-collée).

Tâche :
- Produit un JSON conforme au schéma imposé.
- `title`, `description`, `responsibilities`, `requirements`, `specific_documents`, `expectations`, `specifications` : objets avec clés `fr` et `en`. Si une seule langue est disponible, duplique ou traduis brièvement si tu peux sans inventer de faits métier.
- `work_mode` : uniquement une des valeurs : on-site, hybrid, remote (déduis prudemment ; défaut on-site si absent).
- Salaires : uniquement si explicitement mentionnés ; sinon laisse salary_min / salary_max vides (null) et is_salary_public à false.
- `inferred_skills` / `inferred_benefits` : listes courtes, sans halluciner.
- `education_level_hint` : libellé textuel du diplôme demandé pour rapprochement catalogue côté application.
PROMPT;

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $system),
                new ChatMessage(ChatRole::User, $rawOfferText),
            ],
            options: new GenerationOptions(
                temperature: 0.2,
                maxOutputTokens: 8192,
                responseMimeType: 'application/json',
                responseSchema: OfferFromPlainTextGeminiSchema::responseSchema(),
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return LanguageModelJsonDecoder::decodeObject($result->text);
    }
}
