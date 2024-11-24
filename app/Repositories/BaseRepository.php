<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getQueryBuilder(): Builder
    {
        return $this->model->newQuery();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findByFields(array $conditions): Collection
    {
        $query = $this->getQueryBuilder();

        foreach ($conditions as $field => $values) {
            if (is_array($values)) {
                $query->whereIn($field, $values);
            } else {
                $query->where($field, $values);
            }
        }

        return $query->get();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $object = $this->findById($id);

        if ($object) {
            $object->update($data);
            return $object;
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $object = $this->findById($id);

        return $object ? $object->delete() : false;
    }

    /***************************************************************/
    /* Métodos com QueryBuilder ************************************/
    /***************************************************************/
    /**
     * Definir os campos selecionados
     */
    protected function getSelectFields(): array {
        return ['*'];
    }

    /**
     * Retorna um QueryBuilder para Table List
     */
    abstract public function tableList(Builder $query): Builder;

    /**
     * Retorna um QueryBuilder com os filtros aplicados.
     */
    abstract public function applyFilters(Builder $query, array $filters): Builder;

    /**
     * Retorna um QueryBuilder o Field Searchable.
     */
    public function searchableFilter(string $field, Builder $query, string $search): Builder
    {
        return $query->whereLike($field, "%{$search}%");
    }
}