<?php

namespace App\Repositories;

use App\Models\Jogador;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
        $fieldsSelected = $this->getSelectFields();
        $query = Jogador::query()
            ->select($fieldsSelected)
            ->leftJoin('classes', 'jogadores.classe_id', '=', 'classes.id')
            ->leftJoin('users', 'jogadores.user_id', '=', 'users.id');

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
    public function tableList(Builder $query, int $userId = null): Builder
    {
        $fieldsSelected = $this->getSelectFields();
        if (!empty($userId)) {
            $query->whereNot('user_id', $userId);
        }
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

    public function listByClass(array $conditions): Collection|\Illuminate\Support\Collection
    {
        $query = $this->getQueryBuilder()
            ->leftJoin('classes', 'jogadores.classe_id', '=', 'classes.id')
            ->leftJoin('users', 'jogadores.user_id', '=', 'users.id')
            ->whereNot('user_id', Auth::id());
        foreach ($conditions as $field => $values) {
            if (is_array($values)) {
                $query->whereIn($field, $values);
            } else {
                $query->where($field, $values);
            }
        }
        return $query
            ->orderByDesc('xp')
            ->orderBy('users.name')
            ->get()
            ->mapWithKeys(function ($jogador) {
                $nomeXp = "{$jogador->user->name} (XP: {$jogador->xp})";
                return [$jogador->id => $nomeXp];
            });
    }

    public function getJogadoresAleatorios(array $data): Collection
    {
        $guerreirosQtd = $data['guerreiros'] ?? 1;
        $clerigosQtd = $data['clerigos'] ?? 1;
        $magosQtd = $data['magos'] ?? 1;
        $arqueirosQtd = $data['arqueiros'] ?? 1;

        $guerreiros = $this->getQueryBuilder()
            ->from('jogadores as j')
            ->select('j.*', 'c.nome as classe_nome')
            ->leftJoin('classes as c', 'c.id', '=', 'j.classe_id')
            ->where('classe_id', 1)
            ->orderByRaw('rand()')
            ->limit($guerreirosQtd);

        $clerigos = $this->getQueryBuilder()
            ->from('jogadores as j')
            ->where('classe_id', 2)
            ->select('j.*', 'c.nome as classe_nome')
            ->leftJoin('classes as c', 'c.id', '=', 'j.classe_id')
            ->orderByRaw('rand()')
            ->limit($clerigosQtd);

        $magos = $this->getQueryBuilder()
            ->from('jogadores as j')
            ->where('classe_id', 3)
            ->select('j.*', 'c.nome as classe_nome')
            ->leftJoin('classes as c', 'c.id', '=', 'j.classe_id')
            ->orderByRaw('rand()')
            ->limit($magosQtd);

        $arqueiros = $this->getQueryBuilder()
            ->from('jogadores as j')
            ->where('classe_id', 4)
            ->select('j.*', 'c.nome as classe_nome')
            ->leftJoin('classes as c', 'c.id', '=', 'j.classe_id')
            ->orderByRaw('rand()')
            ->limit($arqueirosQtd);

        return $guerreiros
            ->unionAll($clerigos)
            ->unionAll($magos)
            ->unionAll($arqueiros)
            ->get();
    }
}