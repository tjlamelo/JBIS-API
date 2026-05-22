<?php

declare(strict_types=1);

namespace App\Core\Domain\Workflow\Models;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Workflow\States\ProcessFlowStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

/**
 * Modèle de parcours procédural abstrait (template versionné).
 *
 * Un ProcessFlow décrit une procédure générique (sections + étapes), indépendante
 * d'un pays, d'une offre ou d'un programme. Les FK program_id / offer_id / country_id
 * sont des rattachements optionnels pour filtrage ou pré-remplissage UI — jamais
 * obligatoires pour définir le parcours.
 */
class ProcessFlow extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'process_flows';

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'flow_group_id',
        'version',
        'status',
        'name',
        'description',
        'program_id',
        'offer_id',
        'country_id',
        'estimated_duration_days',
        'total_procedure_fees',
        'file_opening_fee',
        'internal_notes',
    ];

    protected $casts = [
        'version' => 'integer',
        'status' => ProcessFlowStatus::class,
        'program_id' => 'integer',
        'offer_id' => 'integer',
        'country_id' => 'integer',
        'estimated_duration_days' => 'integer',
        'total_procedure_fees' => 'decimal:2',
        'file_opening_fee' => 'decimal:2',
    ];

    /** @param  Builder<$this>  $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProcessFlowStatus::Published->value);
    }

    /** @param  Builder<$this>  $query */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ProcessFlowStatus::Draft->value);
    }

    /**
     * Version publiée la plus récente par groupe (status published, version max).
     *
     * @param  Builder<$this>  $query
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub): void {
            $sub->selectRaw('MAX(id)')
                ->from('process_flows')
                ->where('status', ProcessFlowStatus::Published->value)
                ->whereNull('deleted_at')
                ->groupBy('flow_group_id');
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ProcessFlowSection::class)->orderBy('section_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)->orderBy('step_order');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Clone ce parcours en nouvelle version draft (sections + étapes).
     */
    public function cloneAsNewVersion(): static
    {
        return DB::transaction(function (): static {
            $this->loadMissing(['sections.steps', 'steps']);

            $newVersion = $this->version + 1;

            $existing = static::query()
                ->where('flow_group_id', $this->flow_group_id)
                ->where('version', $newVersion)
                ->exists();

            if ($existing) {
                throw new \RuntimeException(
                    "Un brouillon version {$newVersion} existe déjà pour le groupe {$this->flow_group_id}."
                );
            }

            $newFlow = $this->replicate(['id', 'created_at', 'updated_at', 'deleted_at']);
            $newFlow->version = $newVersion;
            $newFlow->status = ProcessFlowStatus::Draft;
            $newFlow->save();

            $sectionIdMap = [];
            foreach ($this->sections as $section) {
                $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
                $newSection->process_flow_id = $newFlow->id;
                $newSection->save();
                $sectionIdMap[$section->id] = $newSection->id;
            }

            foreach ($this->steps as $step) {
                $newStep = $step->replicate(['id', 'created_at', 'updated_at']);
                $newStep->process_flow_id = $newFlow->id;
                $newStep->process_flow_section_id = $sectionIdMap[$step->process_flow_section_id] ?? null;
                $newStep->save();
            }

            return $newFlow->load(['sections.steps', 'steps']);
        });
    }

    public function publish(): static
    {
        return DB::transaction(function (): static {
            if ($this->status === ProcessFlowStatus::Published) {
                return $this;
            }

            static::query()
                ->where('flow_group_id', $this->flow_group_id)
                ->where('status', ProcessFlowStatus::Published->value)
                ->update(['status' => ProcessFlowStatus::Archived->value]);

            $this->status = ProcessFlowStatus::Published;
            $this->save();

            return $this;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public function versionHistory()
    {
        return static::query()
            ->where('flow_group_id', $this->flow_group_id)
            ->orderByDesc('version')
            ->get();
    }
}
