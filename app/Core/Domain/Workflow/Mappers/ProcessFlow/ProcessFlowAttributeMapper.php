<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Mappers\ProcessFlow;

use App\Core\Domain\Workflow\DTOs\ProcessFlow\ProcessFlowDto;
use App\Core\Domain\Workflow\Models\ProcessFlow;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use Illuminate\Support\Str;

final class ProcessFlowAttributeMapper
{
    public function apply(ProcessFlow $flow, ProcessFlowDto $dto, bool $isCreate): void
    {
        if ($isCreate) {
            $flow->flow_group_id = $dto->flow_group_id !== null && $dto->flow_group_id !== ''
                ? $dto->flow_group_id
                : (string) Str::uuid();
            $flow->version = max(1, $dto->version);
            $flow->status = ProcessFlowStatus::tryFrom($dto->status) ?? ProcessFlowStatus::Draft;
        } else {
            if ($dto->has('flow_group_id') && $dto->flow_group_id !== null && $dto->flow_group_id !== '') {
                $flow->flow_group_id = $dto->flow_group_id;
            }
            if ($dto->has('version')) {
                $flow->version = max(1, $dto->version);
            }
            if ($dto->has('status')) {
                $flow->status = ProcessFlowStatus::tryFrom($dto->status) ?? $flow->status;
            }
        }

        if ($isCreate || $dto->has('name')) {
            if ($dto->name['fr'] !== '' || $dto->name['en'] !== '') {
                $flow->setTranslations('name', $dto->name);
            }
        }

        if ($isCreate || $dto->has('description')) {
            if ($dto->description !== null) {
                $flow->setTranslations('description', $dto->description);
            }
        }

        if ($isCreate || $dto->has('program_id')) {
            $flow->program_id = $dto->program_id;
        }

        if ($isCreate || $dto->has('offer_id')) {
            $flow->offer_id = $dto->offer_id;
        }

        if ($isCreate || $dto->has('country_id')) {
            $flow->country_id = $dto->country_id;
        }

        if ($isCreate || $dto->has('estimated_duration_days')) {
            $flow->estimated_duration_days = $dto->estimated_duration_days;
        }

        if ($isCreate || $dto->has('total_procedure_fees')) {
            $flow->total_procedure_fees = $dto->total_procedure_fees;
        }

        if ($isCreate || $dto->has('file_opening_fee')) {
            $flow->file_opening_fee = $dto->file_opening_fee;
        }

        if ($isCreate || $dto->has('internal_notes')) {
            $flow->internal_notes = $dto->internal_notes;
        }
    }
}
