<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Recruiter\Models\RecruiterProfileRequest;
use App\Core\Domain\Recruiter\Support\RecruiterProfileRequestCriteria;

final class UpdateRecruiterProfileRequestAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(RecruiterProfileRequest $request, array $data): RecruiterProfileRequest
    {
        if (! $request->isEditableByRecruiter()) {
            throw new \InvalidArgumentException(__('Cette demande n’est plus modifiable.'));
        }

        if (isset($data['title'])) {
            $request->title = (string) $data['title'];
        }

        if (isset($data['criteria']) && is_array($data['criteria'])) {
            $existing = is_array($request->criteria) ? $request->criteria : [];
            $request->criteria = array_merge($existing, $data['criteria']);
        }

        if (isset($data['quantity_needed'])) {
            $request->quantity_needed = max(1, min(
                RecruiterProfileRequestCriteria::MAX_QUANTITY,
                (int) $data['quantity_needed'],
            ));
        }

        if (array_key_exists('note', $data)) {
            $request->note = $data['note'];
        }

        $request->save();

        return $request->fresh(['organization', 'submittedBy:id,name,email']);
    }
}
