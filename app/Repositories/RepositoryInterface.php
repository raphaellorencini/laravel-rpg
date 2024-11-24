<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

interface RepositoryInterface
{
    public function getQueryBuilder(): Builder;
    public function getAll(): Collection;
    public function findById(int $id);
    public function findByFields(array $conditions): Collection;
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
    public function tableList(Builder $query): Builder;
    public function searchableFilter(string $field, Builder $query, string $search): Builder;
    public function applyFilters(Builder $query, array $filters): Builder;
}