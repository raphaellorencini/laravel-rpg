<?php

namespace App\Repositories;

use App\Models\Jogador;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class JogadorRepository extends BaseRepository
{
    public function __construct(Jogador $model)
    {
        parent::__construct($model);
    }

    public function getQueryBuilder(): Builder
    {
        return Jogador::query();
    }

    public function getAll(): Collection
    {
        return Jogador::all();
    }

    public function findById(int $id): ?Jogador
    {
        return Jogador::find($id);
    }

    public function findByName($name)
    {
        return Jogador::where('nome', $name)->get();
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
        $query = Jogador::query()->with('classe');

        foreach ($conditions as $field => $values) {
            if (is_array($values)) {
                $query->whereIn($field, $values);
            } else {
                $query->where($field, $values);
            }
        }

        return $query->get();
    }


    public function create(array $data): Jogador
    {
        return Jogador::create($data);
    }

    public function update(int $id, array $data): ?Jogador
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
    protected function getSelectFields(): array
    {
        return [
            'jogadores.*',
            'users.id as user_id',
            'users.name as username',
            'users.email',
            'classes.nome as classe_nome',
        ];
    }

    /**
     * Retorna um QueryBuilder para Table List
     */
    public function tableList(Builder $query): Builder
    {
        $fieldsSelected = $this->getSelectFields();
        return $query
                ->select($fieldsSelected)
                ->leftJoin('classes', 'jogadores.classe_id', '=', 'classes.id')
                ->leftJoin('users', 'jogadores.user_id', '=', 'users.id');
    }

    /**
     * Retorna um QueryBuilder com os filtros aplicados.
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['nome']) && !empty($filters['nome'])) {
            $query
                ->leftJoin('classes', 'jogadores.classe_id', '=', 'classes.id')
                ->where('classes.id', $filters['nome']);
        }
        return $query;
    }
}