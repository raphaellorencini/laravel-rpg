<?php

use Illuminate\Support\Facades\Route;

Route::post('/guildas', [\App\Http\Controllers\GuildasController::class, 'index'])->name('api.guildas.salvar');
Route::post('/sessoes/create', [\App\Http\Controllers\SessoesController::class, 'criarSessao'])->name('api.sessoes.criar');

