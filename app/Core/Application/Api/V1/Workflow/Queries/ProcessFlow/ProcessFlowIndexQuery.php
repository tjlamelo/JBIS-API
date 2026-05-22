<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Queries\ProcessFlow;

use App\Core\Domain\Workflow\Models\ProcessFlow;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProcessFlowIndexQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(ProcessFlow::query());

        $this->allowedFilters([
            AllowedFilter::exact('program_id'),
            AllowedFilter::exact('offer_id'),
            AllowedFilter::exact('country_id'),
            AllowedFilter::exact('flow_group_id'),
            AllowedFilter::exact('status'),
            AllowedFilter::callback('search', function ($query, $value): void {
                $term = '%'.addcslashes((string) $value, '%_\\').'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name->fr', 'like', $term)
                        ->orWhere('name->en', 'like', $term);
                });
            }),
        ])
            ->allowedSorts(['version', 'created_at', 'updated_at'])
            ->defaultSort('-created_at');
    }
}
