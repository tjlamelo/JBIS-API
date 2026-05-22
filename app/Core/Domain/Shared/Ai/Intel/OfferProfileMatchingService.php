<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\OfferRecommendationGeminiSchema;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;

/**
 * Classe des offres publiées (résumé) par rapport à un profil candidat synthétique.
 *
 * @phpstan-type OfferSummary array{id:int,title:string,description:string}
 */
final class OfferProfileMatchingService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
    ) {}

    /**
     * @param  list<OfferSummary>  $offers  Offres à comparer (id + textes courts déjà matérialisés côté application).
     * @return array<string, mixed>
     */
    public function recommend(string $candidateProfileNarrative, array $offers): array
    {
        $system = <<<'PROMPT'
Tu es un conseiller carrière pour la plateforme JBIS. Tu reçois un résumé de profil candidat et une liste d'offres (id + texte).

Tâche :
- Classe les offres par adéquation décroissante (fit_score entre 0 et 1).
- Utilise uniquement les offer_id fournis ; ne crée pas d'offres fictives.
- Sois factuel : base-toi sur le texte fourni ; si une offre manque d'information, le score doit refléter l'incertitude.
- Réponds strictement en JSON selon le schéma imposé.
PROMPT;

        $payload = json_encode([
            'candidate_profile' => $candidateProfileNarrative,
            'offers' => $offers,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $system),
                new ChatMessage(ChatRole::User, $payload),
            ],
            options: new GenerationOptions(
                temperature: 0.25,
                maxOutputTokens: 4096,
                responseMimeType: 'application/json',
                responseSchema: OfferRecommendationGeminiSchema::responseSchema(),
            ),
        );

        $result = $this->languageModel->generateContent($request);

        return LanguageModelJsonDecoder::decodeObject($result->text);
    }

    /**
     * @param  list<OfferSummary>  $offers
     * @return array<string, mixed>
     */
    public function recommendForUser(User $user, array $offers): array
    {
        return $this->recommend(CandidateProfileSnapshotTextBuilder::build($user), $offers);
    }
}
