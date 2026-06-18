<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CatalogAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function resolve(string $resource): array
    {
        $config = config("catalog_admin.resources.{$resource}");

        if (! is_array($config) || empty($config['model'])) {
            throw new NotFoundHttpException(__('Référentiel inconnu.'));
        }

        return $config;
    }

    /**
     * @return class-string<Model>
     */
    public function modelClass(string $resource): string
    {
        /** @var class-string<Model> $class */
        $class = $this->resolve($resource)['model'];

        return $class;
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function listResources(): array
    {
        $resources = config('catalog_admin.resources', []);

        return collect($resources)
            ->map(fn (array $config, string $key) => [
                'key' => $key,
                'label' => (string) ($config['label'] ?? $key),
            ])
            ->values()
            ->all();
    }

    public function newQuery(string $resource): Builder
    {
        $class = $this->modelClass($resource);
        $config = $this->resolve($resource);

        $query = $class::query();

        if (! empty($config['soft_deletes']) && method_exists($class, 'withTrashed')) {
            $query->withTrashed();
        }

        if (! empty($config['with'])) {
            $query->with($config['with']);
        }

        return $query;
    }

    public function find(string $resource, int $id): Model
    {
        $item = $this->newQuery($resource)->whereKey($id)->first();

        if ($item === null) {
            throw new NotFoundHttpException(__('Élément introuvable.'));
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(string $resource, array $validated): Model
    {
        $config = $this->resolve($resource);
        $class = $this->modelClass($resource);
        $payload = $this->preparePayload($config, $validated, null);

        /** @var Model $model */
        $model = new $class;
        $this->fillModel($model, $config, $payload);

        return $this->reload($model, $config);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(string $resource, Model $model, array $validated): Model
    {
        $config = $this->resolve($resource);
        $payload = $this->preparePayload($config, $validated, $model);
        $this->fillModel($model, $config, $payload);

        return $this->reload($model, $config);
    }

    public function delete(string $resource, Model $model): void
    {
        $config = $this->resolve($resource);

        if (! empty($config['soft_deletes']) && method_exists($model, 'delete')) {
            $model->delete();

            return;
        }

        $model->forceDelete();
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $config, array $validated, ?Model $existing): array
    {
        $fillable = $config['fillable'] ?? [];
        $payload = collect($validated)->only($fillable)->all();

        if (in_array('slug', $fillable, true) && empty($payload['slug'])) {
            $payload['slug'] = $this->inferSlug($payload, $existing);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     */
    private function fillModel(Model $model, array $config, array $payload): void
    {
        $translatable = $config['translatable'] ?? [];

        foreach ($payload as $key => $value) {
            if (in_array($key, $translatable, true) && is_array($value)) {
                if (method_exists($model, 'setTranslations')) {
                    $model->setTranslations($key, $value);
                }

                continue;
            }

            if (! in_array($key, $config['fillable'] ?? [], true)) {
                continue;
            }

            $model->{$key} = $value;
        }

        $model->save();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function inferSlug(array $payload, ?Model $existing): string
    {
        foreach (['name', 'label', 'code'] as $field) {
            $value = $payload[$field] ?? ($existing?->{$field} ?? null);

            if (is_array($value)) {
                $label = (string) ($value['en'] ?? $value['fr'] ?? '');
                if ($label !== '') {
                    return Str::slug($label);
                }
            }

            if (is_string($value) && $value !== '') {
                return Str::slug($value);
            }
        }

        throw ValidationException::withMessages([
            'slug' => __('Le slug est requis ou un libellé doit être fourni.'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function reload(Model $model, array $config): Model
    {
        $model->refresh();

        if (! empty($config['with'])) {
            $model->load($config['with']);
        }

        return $model;
    }
}
