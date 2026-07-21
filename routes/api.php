<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\ValidacaoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/validar-cpf/{cpf}', [ValidacaoController::class, 'cpf'])->middleware('throttle:30,1');

// ─── App mobile ─────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::get('/servicos', [ServicoController::class, 'index']);

Route::post('/register/prestador', [RegisterController::class, 'prestador'])->middleware('throttle:10,1');
Route::post('/register/empresa', [RegisterController::class, 'empresa'])->middleware('throttle:10,1');

Route::post('/password/forgot', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1');
