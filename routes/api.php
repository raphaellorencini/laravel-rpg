<?php

use Illuminate\Support\Facades\Route;

Route::post('/guildas', [\App\Http\Controllers\GuildasController::class, 'index'])->name('api.guildas.salvar');
Route::post('/sessoes', [\App\Http\Controllers\SessoesController::class, 'index'])->name('api.sessoes.salvar');
