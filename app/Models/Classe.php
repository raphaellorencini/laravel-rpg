<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model
{
    protected $fillable = [
        'nome'
    ];

    public function jogadores(): HasMany
    {
        return $this->hasMany(Jogador::class);
    }
}
