<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Services;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Identity\Models\DocumentType;
use Illuminate\Support\Collection;

/**
 * Fait le lien entre le catalogue required_documents (offre/programme)
 * et les document_types utilisés dans user_documents.
 */
final class RequiredDocumentTypeMapper
{
    /** @var array<string, string> required_document.slug => document_types.storage_slug */
    private const SLUG_ALIASES = [
        'passeport-valide' => 'passeport',
        'diplome-le-plus-eleve' => 'diplome',
        'test-de-langue-ieltstef' => 'certification-pro',
        'preuve-de-fonds' => 'rib',
        'certificat-de-visite-medicale' => 'certificat-medical',
        'portfolio' => 'autre',
        'lettre-motivation' => 'lettre-motivation',
        'cv' => 'cv',
        'casier-judiciaire' => 'casier-judiciaire',
        'photo-identite' => 'photo-identite',
        'extrait-de-naissance' => 'acte-naissance',
        'justificatif-domicile' => 'justificatif-domicile',
    ];

    /** @var Collection<int, DocumentType>|null */
    private ?Collection $documentTypes = null;

    public function resolveDocumentTypeId(RequiredDocument $requiredDocument): ?int
    {
        $type = $this->resolveDocumentType($requiredDocument);

        return $type?->id;
    }

    public function resolveDocumentType(RequiredDocument $requiredDocument): ?DocumentType
    {
        $slug = (string) $requiredDocument->slug;
        $storageSlug = self::SLUG_ALIASES[$slug] ?? $slug;

        $types = $this->documentTypes();

        $exact = $types->firstWhere('storage_slug', $storageSlug);
        if ($exact !== null) {
            return $exact;
        }

        return $types->first(function (DocumentType $type) use ($slug, $storageSlug): bool {
            $candidate = (string) $type->storage_slug;

            return str_starts_with($slug, $candidate)
                || str_starts_with($candidate, $slug)
                || str_starts_with($slug, $storageSlug);
        });
    }

    /**
     * @return Collection<int, DocumentType>
     */
    private function documentTypes(): Collection
    {
        if ($this->documentTypes === null) {
            $this->documentTypes = DocumentType::query()
                ->where('is_active', true)
                ->get();
        }

        return $this->documentTypes;
    }
}
