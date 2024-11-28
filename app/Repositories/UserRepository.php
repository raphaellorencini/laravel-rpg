<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }


    public function getQueryBuilder(): Builder
    {
        return User::query();
    }

    public function getAll(): Collection
    {
        return User::all();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByName($name)
    {
        return User::where('nome', $name)->get();
    }

    /**
     * @param array $conditions
     * @return Collection
     * Pode ser usado assim:
     * findByMultipleConditions(['nome' => 'abc'])
     * findByMultipleConditions(['nome' => ['abc', 'def'],])
     * findByMultipleConditions(['name' => 'abc', 'active' => true,])
     */
    public function findByFields(array $conditions): Collection
    {
        $query = User::query();

        foreach ($conditions as $field => $values) {
            if (is_array($values)) {
                $query->whereIn($field, $values);
            } else {
                $query->where($field, $values);
            }
        }

        return $query->get();
    }


    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): ?User
    {
        $obj = $this->findById($id);

        if ($obj) {
            $obj->update($data);
            return $obj;
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $obj = $this->findById($id);

        if ($obj) {
            return $obj->delete();
        }

        return false;
    }

    /***************************************************************/
    /* Métodos com QueryBuilder ************************************/
    /***************************************************************/

    /**
     * Retorna um QueryBuilder para Table List
     */
    public function tableList(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Retorna um QueryBuilder com os filtros aplicados.
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['nome']) && !empty($filters['nome'])) {
            $query->where('id', $filters['nome']);
        }
        return $query;
    }
}