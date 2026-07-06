<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\OfferFromPlainTextGeminiSchema;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelTransportException;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;

/**
 * Transforme une offre rédigée en texte libre en champs structurés (alignés sur le modèle Offer + champs enrichis).
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
        return $this->structureDraftWithContext($rawOfferText, null);
    }

    /**
     * @param  array<string, mixed>|null  $context  Contexte formulaire (trade_id, trade_name, country_id…).
     * @return array<string, mixed>
     */
    public function structureDraftWithContext(string $rawOfferText, ?array $context = null, string $scope = 'full'): array
    {
        $rawOfferText = trim($rawOfferText);
        if ($rawOfferText === '') {
            throw new \InvalidArgumentException('Le texte de l\'offre est vide.');
        }

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, JobOfferFromPlainTextSystemPrompt::build($context, $scope)),
                new ChatMessage(ChatRole::User, $rawOfferText),
            ],
            options: new GenerationOptions(
                temperature: 0.1,
                maxOutputTokens: 4096,
                responseMimeType: 'application/json',
                responseSchema: OfferFromPlainTextGeminiSchema::responseSchema($scope),
            ),
        );

        $result = $this->languageModel->generateContent($request);

        try {
            return LanguageModelJsonDecoder::decodeObject($result->text);
        } catch (LanguageModelInvalidJsonException $exception) {
            if ($result->finishReason === 'MAX_TOKENS') {
                throw new LanguageModelTransportException(
                    'La réponse IA a été tronquée (trop longue). Utilisez le bouton IA de la section « Descriptif & Missions » ou réessayez avec un texte plus court.',
                    previous: $exception
                );
            }

            throw $exception;
        }
    }
}
