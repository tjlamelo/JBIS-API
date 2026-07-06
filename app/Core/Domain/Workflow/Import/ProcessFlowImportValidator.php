<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Import;

use App\Core\Domain\Identity\Models\DocumentType;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportFlowData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportIssue;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportPayload;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportSectionData;
use App\Core\Domain\Workflow\Import\DTOs\ProcessFlowImportStepData;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;
use BackedEnum;

final class ProcessFlowImportValidator
{
    /** @var array<string, int>|null */
    private ?array $documentTypeCodeMap = null;

    public function __construct(
        private readonly ProcessFlowImportDerivedTotals $derivedTotals,
    ) {}

    /**
     * @return list<ProcessFlowImportIssue>
     */
    public function validate(ProcessFlowImportPayload $payload): array
    {
        $issues = [];
        $seenFlowKeys = [];

        foreach ($payload->flows as $flowIndex => $flow) {
            $flowPath = "flows[{$flowIndex}]";

            if ($flow->flowKey === '') {
                $issues[] = new ProcessFlowImportIssue($flowPath, 'flow_key', __('La clé de parcours (flow_key) est obligatoire.'));
                continue;
            }

            if (isset($seenFlowKeys[$flow->flowKey])) {
                $issues[] = new ProcessFlowImportIssue(
                    $flowPath,
                    'flow_key',
                    __('La clé « :key » est dupliquée dans le fichier.', ['key' => $flow->flowKey]),
                );
            }
            $seenFlowKeys[$flow->flowKey] = true;

            if ($flow->countryCode === '') {
                $issues[] = new ProcessFlowImportIssue($flowPath, 'country_code', __('Le code pays est obligatoire.'));
            } elseif (! Country::query()->where('code', strtoupper($flow->countryCode))->exists()) {
                $issues[] = new ProcessFlowImportIssue(
                    $flowPath,
                    'country_code',
                    __('Le code pays « :code » est introuvable. Utilisez un code de la feuille _Countries du modèle.', ['code' => $flow->countryCode]),
                );
            }

            if ($flow->name['fr'] === '' && $flow->name['en'] === '') {
                $issues[] = new ProcessFlowImportIssue($flowPath, 'name', __('Le nom du parcours (fr ou en) est obligatoire.'));
            }

            foreach (['file_opening_fee' => $flow->fileOpeningFee, 'total_procedure_fees' => $flow->totalProcedureFees] as $field => $value) {
                if ($value !== null && $value < 0) {
                    $issues[] = new ProcessFlowImportIssue(
                        $flowPath,
                        $field,
                        __('Le montant « :field » ne peut pas être négatif.', ['field' => $field]),
                    );
                }
            }

            if ($flow->sections === []) {
                $issues[] = new ProcessFlowImportIssue($flowPath, 'sections', __('Au moins une section est requise.'));
            }

            $issues = array_merge($issues, $this->validateSections($flow, $flowPath));
            $issues = array_merge($issues, $this->validateDerivedTotals($flow, $flowPath));
        }

        return $issues;
    }

    /**
     * @return list<ProcessFlowImportIssue>
     */
    private function validateSections(ProcessFlowImportFlowData $flow, string $flowPath): array
    {
        $issues = [];
        $sectionKeys = [];
        $globalStepOrders = [];

        foreach ($flow->sections as $sectionIndex => $section) {
            $sectionPath = "{$flowPath}.sections[{$sectionIndex}]";

            if ($section->sectionKey === '') {
                $issues[] = new ProcessFlowImportIssue(
                    $sectionPath,
                    'section_key',
                    __('Impossible de déterminer la clé de section — renseignez section_key ou title_fr.'),
                );
                continue;
            }

            if (isset($sectionKeys[$section->sectionKey])) {
                $issues[] = new ProcessFlowImportIssue(
                    $sectionPath,
                    'section_key',
                    __('La section « :key » est dupliquée pour ce parcours.', ['key' => $section->sectionKey]),
                );
            }
            $sectionKeys[$section->sectionKey] = true;

            if ($section->title['fr'] === '' && $section->title['en'] === '') {
                $issues[] = new ProcessFlowImportIssue($sectionPath, 'title', __('Le titre de section (fr ou en) est obligatoire.'));
            }

            $localOrders = [];
            foreach ($section->steps as $stepIndex => $step) {
                $stepPath = "{$sectionPath}.steps[{$stepIndex}]";
                $issues = array_merge($issues, $this->validateStep($step, $stepPath));

                if (isset($localOrders[$step->stepOrder])) {
                    $issues[] = new ProcessFlowImportIssue(
                        $stepPath,
                        'step_order',
                        __('Le numéro d\'étape :order est dupliqué dans la section « :section ».', [
                            'order' => $step->stepOrder,
                            'section' => $section->sectionKey,
                        ]),
                    );
                }
                $localOrders[$step->stepOrder] = true;

                if (isset($globalStepOrders[$step->globalStepOrder])) {
                    $issues[] = new ProcessFlowImportIssue(
                        $stepPath,
                        'global_step_order',
                        __('Le numéro d\'étape global :order est dupliqué.', ['order' => $step->globalStepOrder]),
                    );
                }
                $globalStepOrders[$step->globalStepOrder] = true;

                foreach ($step->documentTypeCodes as $docIndex => $code) {
                    if ($code === '') {
                        continue;
                    }

                    if (! $this->documentTypeExists($code)) {
                        $issues[] = new ProcessFlowImportIssue(
                            "{$stepPath}.required_documents[{$docIndex}]",
                            'document_type_code',
                            __('Le type de document « :code » est introuvable. Utilisez un code exact de la feuille _DocumentTypes du modèle (ex. PASSPORT, CV, DIPLOMA).', ['code' => $code]),
                        );
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * @return list<ProcessFlowImportIssue>
     */
    private function validateDerivedTotals(ProcessFlowImportFlowData $flow, string $flowPath): array
    {
        $issues = [];

        $calculatedOpening = $this->derivedTotals->fileOpeningFee($flow);
        if (! $this->derivedTotals->amountsMatch($flow->fileOpeningFee, $calculatedOpening)) {
            $issues[] = new ProcessFlowImportIssue(
                $flowPath,
                'file_opening_fee',
                __('file_opening_fee fourni = :provided, mais somme des étapes FILE_OPENING = :calculated — laissez vide pour calcul automatique ou corrigez l\'un des deux.', [
                    'provided' => number_format((float) $flow->fileOpeningFee, 2, '.', ''),
                    'calculated' => number_format($calculatedOpening, 2, '.', ''),
                ]),
            );
        }

        $calculatedProcedure = $this->derivedTotals->procedureFees($flow);
        if (! $this->derivedTotals->amountsMatch($flow->totalProcedureFees, $calculatedProcedure)) {
            $issues[] = new ProcessFlowImportIssue(
                $flowPath,
                'total_procedure_fees',
                __('total_procedure_fees fourni = :provided, mais somme des étapes de paiement (hors FILE_OPENING) = :calculated — laissez vide pour calcul automatique ou corrigez l\'un des deux.', [
                    'provided' => number_format((float) $flow->totalProcedureFees, 2, '.', ''),
                    'calculated' => number_format($calculatedProcedure, 2, '.', ''),
                ]),
            );
        }

        $calculatedDuration = $this->derivedTotals->estimatedDurationDays($flow);
        if ($flow->estimatedDurationDays !== null && $calculatedDuration !== null
            && (int) $flow->estimatedDurationDays !== $calculatedDuration) {
            $issues[] = new ProcessFlowImportIssue(
                $flowPath,
                'estimated_duration_days',
                __('estimated_duration_days fourni = :provided, mais somme des durées d\'étapes = :calculated — corrigez l\'un des deux.', [
                    'provided' => (string) $flow->estimatedDurationDays,
                    'calculated' => (string) $calculatedDuration,
                ]),
            );
        }

        return $issues;
    }

    /**
     * @return list<ProcessFlowImportIssue>
     */
    private function validateStep(ProcessFlowImportStepData $step, string $stepPath): array
    {
        $issues = [];

        if ($step->stepOrder < 1) {
            $issues[] = new ProcessFlowImportIssue($stepPath, 'step_order', __('Le numéro d\'étape doit être supérieur ou égal à 1.'));
        }

        if (! $this->isValidEnumValue(ProcessStepType::class, $step->stepType)) {
            $issues[] = new ProcessFlowImportIssue(
                $stepPath,
                'step_type',
                __('Le type d\'étape « :value » est invalide.', ['value' => $step->stepType]),
            );
        }

        if ($step->paymentType !== null && $step->paymentType !== '' && ! $this->isValidEnumValue(PaymentType::class, $step->paymentType)) {
            $issues[] = new ProcessFlowImportIssue(
                $stepPath,
                'payment_type',
                __('Le type de paiement « :value » est invalide.', ['value' => $step->paymentType]),
            );
        }

        if ($step->responsibleParty !== null && $step->responsibleParty !== '' && ! $this->isValidEnumValue(ResponsibleParty::class, $step->responsibleParty)) {
            $issues[] = new ProcessFlowImportIssue(
                $stepPath,
                'responsible_party',
                __('Le responsable « :value » est invalide.', ['value' => $step->responsibleParty]),
            );
        }

        if ($step->amount < 0) {
            $issues[] = new ProcessFlowImportIssue($stepPath, 'amount', __('Le montant ne peut pas être négatif.'));
        }

        if ($step->title['fr'] === '' && $step->title['en'] === '') {
            $issues[] = new ProcessFlowImportIssue($stepPath, 'title', __('Le titre de l\'étape (fr ou en) est obligatoire.'));
        }

        return $issues;
    }

    /**
     * @param  class-string<BackedEnum>  $enumClass
     */
    private function isValidEnumValue(string $enumClass, string $value): bool
    {
        return $enumClass::tryFrom(strtoupper(trim($value))) !== null;
    }

    private function documentTypeExists(string $code): bool
    {
        return isset($this->documentTypeCodeMap()[strtoupper(trim($code))]);
    }

    /**
     * @return array<string, int>
     */
    public function documentTypeCodeMap(): array
    {
        if ($this->documentTypeCodeMap !== null) {
            return $this->documentTypeCodeMap;
        }

        $this->documentTypeCodeMap = DocumentType::query()
            ->pluck('id', 'code')
            ->mapWithKeys(static fn (mixed $id, string $code): array => [strtoupper($code) => (int) $id])
            ->all();

        return $this->documentTypeCodeMap;
    }

    /**
     * @return array{flow_group_id: string, version: int, is_new_group: bool}
     */
    public function resolveVersioning(string $flowKey): array
    {
        $existing = ProcessFlow::query()
            ->where('import_key', $flowKey)
            ->orderByDesc('version')
            ->first();

        if ($existing === null) {
            return [
                'flow_group_id' => (string) \Illuminate\Support\Str::uuid(),
                'version' => 1,
                'is_new_group' => true,
            ];
        }

        $maxVersion = (int) ProcessFlow::query()
            ->where('flow_group_id', $existing->flow_group_id)
            ->max('version');

        return [
            'flow_group_id' => $existing->flow_group_id,
            'version' => $maxVersion + 1,
            'is_new_group' => false,
        ];
    }
}
