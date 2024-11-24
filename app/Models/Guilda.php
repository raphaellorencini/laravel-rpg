<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guilda extends Model
{
    public function jogadores(): BelongsToMany {
        return $this->belongsToMany(User::class, 'guilda_jogador');
    }
}
