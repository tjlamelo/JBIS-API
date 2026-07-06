<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Workflow\Models\ProcessFlowSection;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;
use BackedEnum;

final class ProcessFlowImportTemplateCatalog
{
    /**
     * @return list<array{code: string, label_fr: string, label_en: string}>
     */
    public function documentTypes(): array
    {
        return DocumentType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->map(static function (DocumentType $type): array {
                $label = $type->label;
                if (is_string($label)) {
                    $label = json_decode($label, true) ?? [];
                }

                return [
                    'code' => strtoupper($type->code),
                    'label_fr' => (string) ($label['fr'] ?? $type->code),
                    'label_en' => (string) ($label['en'] ?? $label['fr'] ?? $type->code),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{code: string, name_fr: string, name_en: string}>
     */
    public function countries(): array
    {
        return Country::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(static function (Country $country): array {
                $name = $country->getTranslations('name');

                return [
                    'code' => strtoupper($country->code),
                    'name_fr' => (string) ($name['fr'] ?? $country->code),
                    'name_en' => (string) ($name['en'] ?? $name['fr'] ?? $country->code),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{type: string, code: string, label_fr: string}>
     */
    public function enums(): array
    {
        $rows = [];

        foreach ($this->enumRows(ProcessStepType::class, 'step_type') as $row) {
            $rows[] = $row;
        }
        foreach ($this->enumRows(PaymentType::class, 'payment_type') as $row) {
            $rows[] = $row;
        }
        foreach ($this->enumRows(ResponsibleParty::class, 'responsible_party') as $row) {
            $rows[] = $row;
        }

        foreach ($this->acceptedBankCodes() as $code => $label) {
            $rows[] = [
                'type' => 'accepted_bank',
                'code' => $code,
                'label_fr' => $label,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{key: string, title_fr: string, title_en: string}>
     */
    public function sectionKeys(): array
    {
        $ids = ProcessFlowSection::query()
            ->selectRaw('`key`, MAX(id) as id')
            ->groupBy('key')
            ->orderBy('key')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return [
                ['key' => 'ouverture', 'title_fr' => 'Ouverture de dossier', 'title_en' => 'File opening'],
                ['key' => 'procedure', 'title_fr' => 'Procédure', 'title_en' => 'Procedure'],
            ];
        }

        $sections = ProcessFlowSection::query()->whereIn('id', $ids)->get()->keyBy('id');

        return $ids
            ->map(static function (int $id) use ($sections): array {
                $section = $sections->get($id);
                $title = $section?->getTranslations('title') ?? [];

                return [
                    'key' => (string) ($section?->key ?? ''),
                    'title_fr' => (string) ($title['fr'] ?? $section?->key ?? ''),
                    'title_en' => (string) ($title['en'] ?? $title['fr'] ?? $section?->key ?? ''),
                ];
            })
            ->filter(static fn (array $row): bool => $row['key'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  class-string<BackedEnum>  $enumClass
     * @return list<array{type: string, code: string, label_fr: string}>
     */
    private function enumRows(string $enumClass, string $type): array
    {
        $labels = $this->enumLabels();

        return array_map(
            static function (BackedEnum $case) use ($type, $labels): array {
                $code = (string) $case->value;

                return [
                    'type' => $type,
                    'code' => $code,
                    'label_fr' => $labels[$code] ?? $code,
                ];
            },
            $enumClass::cases(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function enumLabels(): array
    {
        return [
            'DOCUMENT_COLLECTION' => 'Collecte de documents',
            'PAYMENT' => 'Paiement',
            'SERVICE' => 'Service JBIS',
            'INTERVIEW' => 'Entretien',
            'SIGNING' => 'Signature',
            'ADMINISTRATIVE' => 'Administratif',
            'IMMIGRATION_EXIT' => 'Sortie immigration',
            'INFO' => 'Information',
            'FILE_OPENING' => 'Ouverture de dossier (hors total_procedure_fees)',
            'PROCEDURE_INSTALMENT' => 'Versement procédure (inclus dans total_procedure_fees)',
            'BLOCKED_ACCOUNT' => 'Compte bloqué',
            'CANDIDATE' => 'Candidat',
            'JBIS' => 'JBIS',
            'EMPLOYER' => 'Employeur',
            'AUTHORITY' => 'Autorité',
            'SHARED' => 'Partagé',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function acceptedBankCodes(): array
    {
        return [
            'ORIS_FINANCE' => 'Oris Finance',
            'SCB' => 'SCB Cameroun',
            'SCB_BANK' => 'Alias SCB (préférer SCB)',
        ];
    }
}
