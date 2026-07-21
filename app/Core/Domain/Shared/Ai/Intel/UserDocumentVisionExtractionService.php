<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Identity\Models\UserDocument;
use App\Core\Domain\Identity\Services\Document\DocumentVisionInputResolver;
use App\Core\Domain\Shared\Ai\Contracts\LanguageModelClientInterface;
use App\Core\Domain\Shared\Ai\DTOs\ChatMessage;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerationOptions;
use App\Core\Domain\Shared\Ai\Enums\ChatRole;
use App\Core\Domain\Shared\Ai\Support\LanguageModelJsonDecoder;
use App\Core\Domain\Shared\Ai\Support\ProfileBundleDraftNormalizer;
use Illuminate\Support\Facades\Log;

/**
 * Envoie une ou plusieurs images au modèle vision et retourne un brouillon JSON structuré.
 */
final class UserDocumentVisionExtractionService
{
    public function __construct(
        private readonly LanguageModelClientInterface $languageModel,
        private readonly DocumentVisionInputResolver $visionInputResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function extractFromDocument(UserDocument $document): array
    {
        $document->loadMissing('documentType');
        $typeCode = (string) ($document->documentType?->code ?? '');

        if (! DocumentExtractionProfileRegistry::isExtractable($typeCode)) {
            throw new \InvalidArgumentException(sprintf('Extraction IA non configurée pour le type %s.', $typeCode));
        }

        if (! DocumentExtractionProfileRegistry::supportsExtractableMime($document->mime_type)) {
            throw new \InvalidArgumentException('Format de fichier non pris en charge pour l\'extraction IA.');
        }

        $filePath = (string) ($document->file_path ?? '');
        $imageInput = $this->visionInputResolver->fromStoragePath($filePath, $document->mime_type);

        return $this->extractFromImageInputs($document, [$imageInput]);
    }

    /**
     * @param  list<string>  $imageInputs URLs ou data-URL base64
     * @return array<string, mixed>
     */
    public function extractFromImageInputs(UserDocument $document, array $imageInputs): array
    {
        $document->loadMissing('documentType');
        $typeCode = (string) ($document->documentType?->code ?? '');
        $imageInputs = array_values(array_filter($imageInputs, static fn (string $value): bool => $value !== ''));

        if ($imageInputs === []) {
            throw new \RuntimeException('Aucune image fournie pour l\'analyse vision.');
        }

        $firstInput = $imageInputs[0];

        Log::info('[document_extraction] Appel vision', [
            'user_document_id' => $document->id,
            'document_type' => $typeCode,
            'mime_type' => $document->mime_type,
            'vision_driver' => (string) config('ai.document_extraction.driver', 'groq'),
            'vision_input_mode' => (string) config('ai.document_extraction.vision_input', 'base64'),
            'image_source' => str_starts_with($firstInput, 'data:') ? 'base64' : 'url',
            'image_count' => count($imageInputs),
        ]);

        $profile = DocumentExtractionProfileRegistry::resolve($typeCode);
        $userPrompt = count($imageInputs) > 1
            ? sprintf(
                'Analyse ces %d pages de document et renvoie un seul JSON fusionné. '
                .'Pour un CV : remplis obligatoirement experiences, educations, internships, languages, skills quand ils sont visibles — pas seulement user_profile.',
                count($imageInputs),
            )
            : 'Analyse ce document et renvoie uniquement le JSON structuré demandé. '
                .'Pour un CV : extrais toutes les sections (pas seulement l\'identité).';

        $request = new GenerateContentRequest(
            messages: [
                new ChatMessage(ChatRole::System, $profile['system']),
                new ChatMessage(
                    ChatRole::User,
                    $userPrompt,
                    imageUrls: $imageInputs,
                ),
            ],
            options: new GenerationOptions(
                temperature: 0.1,
                maxOutputTokens: 8192,
                responseMimeType: 'application/json',
                responseSchema: $profile['schema'],
            ),
        );

        $result = $this->languageModel->generateContent($request);

        $draft = LanguageModelJsonDecoder::decodeObject($result->text);
        $draft = ProfileBundleDraftNormalizer::normalize($draft);

        Log::info('[document_extraction] Réponse vision décodée', [
            'user_document_id' => $document->id,
            'finish_reason' => $result->finishReason,
            'text_length' => strlen($result->text),
            'draft_keys' => array_keys($draft),
        ]);

        return $draft;
    }
}
