<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Mappers\ProcessStep;

use App\Core\Domain\Workflow\DTOs\ProcessStep\ProcessStepDto;
use App\Core\Domain\Workflow\Models\ProcessStep;
use App\Core\Domain\Workflow\States\PaymentType;
use App\Core\Domain\Workflow\States\ProcessStepType;
use App\Core\Domain\Workflow\States\ResponsibleParty;

final class ProcessStepAttributeMapper
{
    public function apply(ProcessStep $step, ProcessStepDto $dto, bool $isCreate): void
    {
        if ($isCreate || $dto->has('process_flow_id')) {
            $step->process_flow_id = $dto->process_flow_id;
        }

        if ($isCreate || $dto->has('process_flow_section_id')) {
            $step->process_flow_section_id = $dto->process_flow_section_id;
        }

        if ($isCreate || $dto->has('step_type')) {
            $step->step_type = ProcessStepType::tryFrom($dto->step_type)?->value ?? ProcessStepType::Info->value;
        }

        if ($isCreate || $dto->has('payment_type')) {
            $step->payment_type = $dto->payment_type !== null
                ? (PaymentType::tryFrom($dto->payment_type)?->value)
                : null;
        }

        if ($isCreate || $dto->has('responsible_party')) {
            $step->responsible_party = ResponsibleParty::tryFrom($dto->responsible_party)?->value
                ?? ResponsibleParty::Candidate->value;
        }

        if ($isCreate || $dto->has('step_order')) {
            $step->step_order = max(1, $dto->step_order);
        }

        if ($isCreate || $dto->has('title')) {
            $step->setTranslations('title', $dto->title);
        }

        if ($isCreate || $dto->has('description')) {
            if ($dto->description !== null) {
                $step->setTranslations('description', $dto->description);
            }
        }

        if ($isCreate || $dto->has('internal_note')) {
            $step->internal_note = $dto->internal_note;
        }

        if ($isCreate || $dto->has('is_blocking')) {
            $step->is_blocking = $dto->is_blocking;
        }

        if ($isCreate || $dto->has('is_required')) {
            $step->is_required = $dto->is_required;
        }

        if ($isCreate || $dto->has('default_amount')) {
            $step->default_amount = $dto->default_amount;
        }

        if ($isCreate || $dto->has('accepted_banks')) {
            $step->accepted_banks = $dto->accepted_banks;
        }

        if ($isCreate || $dto->has('requires_documents')) {
            $step->requires_documents = $dto->requires_documents;
        }

        if ($isCreate || $dto->has('document_type_ids')) {
            $step->document_type_ids = $dto->document_type_ids;
        }

        if ($isCreate || $dto->has('estimated_duration_days')) {
            $step->estimated_duration_days = $dto->estimated_duration_days;
        }

        if ($isCreate || $dto->has('sla_alert_days')) {
            $step->sla_alert_days = $dto->sla_alert_days;
        }
    }
}
