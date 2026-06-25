<?php

use App\Http\Controllers\Api\ValidacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/validar-cpf/{cpf}', [ValidacaoController::class, 'cpf'])->middleware('throttle:30,1');
