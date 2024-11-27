<?php

namespace App\Repositories;

use App\Models\Guilda;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class GuildaRepository extends BaseRepository
{
    public function __construct(Guilda $model)
    {
        parent::__construct($model);
    }


    public function getQueryBuilder(): Builder
    {
        return Guilda::query();
    }

    public function getAll(): Collection
    {
        return Guilda::all();
    }

    public function findById(int $id): ?Guilda
    {
        return Guilda::find($id);
    }

    public function findByName($name)
    {
        return Guilda::where('nome', $name)->get();
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
        $query = Guilda::query()->with('jogadores');

        foreach ($conditions as $field => $values) {
            if (is_array($values)) {
                $query->whereIn($field, $values);
            } else {
                $query->where($field, $values);
            }
        }

        return $query->get();
    }


    public function create(array $data): Guilda
    {
        return Guilda::create($data);
    }

    public function update(int $id, array $data): ?Guilda
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
    public function tableList(Builder $query, int $userId = null): Builder
    {
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
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


    public function getConfirmados() {
        return User::where('confirmado', true)->get();
    }

    public function criarGuilda($dados): Guilda {
        return Guilda::create($dados);
    }
}