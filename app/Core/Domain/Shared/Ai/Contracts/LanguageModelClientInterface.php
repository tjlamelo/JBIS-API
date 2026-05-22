<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Contracts;

use App\Core\Domain\Shared\Ai\DTOs\GenerateContentRequest;
use App\Core\Domain\Shared\Ai\DTOs\GenerateContentResult;

/**
 * Port applicatif pour un client de modèle de langage.
 *
 * Les implémentations concrètes (Gemini, OpenAI, etc.) sont branchées via la config `ai`.
 */
interface LanguageModelClientInterface
{
    public function generateContent(GenerateContentRequest $request): GenerateContentResult;
}
