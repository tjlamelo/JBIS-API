<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Intel;

use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\BirthCertificateGeminiSchema;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\CertificationDocumentGeminiSchema;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\DiplomaDocumentGeminiSchema;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\IdentityDocumentGeminiSchema;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\ProfileBundleGeminiSchema;
use App\Core\Domain\Shared\Ai\Schemas\GeminiResponse\WorkCertificateGeminiSchema;

/**
 * Prompts et schémas JSON par code {@see DocumentType::code}.
 */
final class DocumentExtractionProfileRegistry
{
    /** @var list<string> */
    public const EXTRACTABLE_TYPE_CODES = [
        'CV',
        'ID_CARD',
        'PASSPORT',
        'RESIDENCE_PERMIT',
        'DRIVING_LICENSE',
        'VISA',
        'BIRTH_CERTIFICATE',
        'DIPLOMA',
        'TRANSCRIPT',
        'SUCCESS_CERTIFICATE',
        'WORK_CERTIFICATE',
        'PROFESSIONAL_CERTIFICATION',
        'TRAINING_CERTIFICATE',
    ];

    /** @var list<string> */
    private const IDENTITY_TYPE_CODES = [
        'ID_CARD',
        'PASSPORT',
        'RESIDENCE_PERMIT',
        'DRIVING_LICENSE',
        'VISA',
    ];

    public static function isExtractable(string $documentTypeCode): bool
    {
        return in_array(strtoupper($documentTypeCode), self::EXTRACTABLE_TYPE_CODES, true);
    }

    public static function isIdentityDocument(string $documentTypeCode): bool
    {
        return in_array(strtoupper($documentTypeCode), self::IDENTITY_TYPE_CODES, true);
    }

    public static function supportsExtractableMime(?string $mimeType): bool
    {
        if ($mimeType === null || $mimeType === '') {
            return false;
        }

        $mime = strtolower($mimeType);

        return str_starts_with($mime, 'image/') || $mime === 'application/pdf';
    }

    /** @deprecated Utiliser {@see supportsExtractableMime()} */
    public static function supportsVisionMime(?string $mimeType): bool
    {
        return self::supportsExtractableMime($mimeType);
    }

    /**
     * @return array{system: string, schema: array<string, mixed>}
     */
    public static function resolve(string $documentTypeCode): array
    {
        $code = strtoupper($documentTypeCode);

        if (self::isIdentityDocument($code)) {
            return [
                'system' => IdentityDocumentExtractionSystemPrompt::text($code),
                'schema' => IdentityDocumentGeminiSchema::responseSchema(),
            ];
        }

        return match ($code) {
            'CV' => [
                'system' => CvExtractionSystemPrompt::text(),
                'schema' => ProfileBundleGeminiSchema::responseSchema(),
            ],
            'BIRTH_CERTIFICATE' => [
                'system' => BirthCertificateExtractionSystemPrompt::text(),
                'schema' => BirthCertificateGeminiSchema::responseSchema(),
            ],
            'DIPLOMA', 'TRANSCRIPT', 'SUCCESS_CERTIFICATE' => [
                'system' => DiplomaDocumentExtractionSystemPrompt::text(),
                'schema' => DiplomaDocumentGeminiSchema::responseSchema(),
            ],
            'WORK_CERTIFICATE' => [
                'system' => WorkCertificateExtractionSystemPrompt::text(),
                'schema' => WorkCertificateGeminiSchema::responseSchema(),
            ],
            'PROFESSIONAL_CERTIFICATION', 'TRAINING_CERTIFICATE' => [
                'system' => CertificationDocumentExtractionSystemPrompt::text($code),
                'schema' => CertificationDocumentGeminiSchema::responseSchema(),
            ],
            default => throw new \InvalidArgumentException(sprintf('Type de document non pris en charge : %s', $documentTypeCode)),
        };
    }
}
