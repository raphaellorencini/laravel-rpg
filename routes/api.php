<?php

use Illuminate\Support\Facades\Route;

Route::get('/guildas', [\App\Http\Controllers\GuildasController::class, 'index']);
