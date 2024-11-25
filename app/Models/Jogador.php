<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jogador extends Model
{
    protected $table = 'jogadores';

    protected $fillable = [
        'classe_id',
        'xp',
        'confirmado',
        'image',
        'user_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }
}
