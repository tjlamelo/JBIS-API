<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Support;

final class PartnerCohortDocumentDefaults
{
    /** @return list<array{document_type_code: string, is_mandatory: bool, sort_order: int}> */
    public static function requiredDocuments(): array
    {
        return [
            ['document_type_code' => 'CV', 'is_mandatory' => true, 'sort_order' => 10],
            ['document_type_code' => 'ID_CARD', 'is_mandatory' => true, 'sort_order' => 20],
            ['document_type_code' => 'TRANSCRIPT', 'is_mandatory' => true, 'sort_order' => 30],
            ['document_type_code' => 'DIPLOMA', 'is_mandatory' => false, 'sort_order' => 40],
            ['document_type_code' => 'INTERNSHIP_AGREEMENT', 'is_mandatory' => false, 'sort_order' => 50],
        ];
    }
}
