<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Empresa\DashboardController as EmpresaDashboardController;
use App\Http\Controllers\Api\Empresa\PropostasController as EmpresaPropostasController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\Prestador\AgendaController as PrestadorAgendaController;
use App\Http\Controllers\Api\Prestador\DashboardController as PrestadorDashboardController;
use App\Http\Controllers\Api\Prestador\DemandasController as PrestadorDemandasController;
use App\Http\Controllers\Api\Prestador\PropostasController as PrestadorPropostasController;
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

Route::middleware(['auth:sanctum', 'api.provider'])->prefix('prestador')->group(function () {
    Route::get('/dashboard', [PrestadorDashboardController::class, 'index']);

    Route::get('/demandas', [PrestadorDemandasController::class, 'index']);
    Route::get('/demandas/{demand}', [PrestadorDemandasController::class, 'show']);
    Route::post('/demandas/proposta', [PrestadorDemandasController::class, 'enviarProposta']);

    Route::get('/propostas', [PrestadorPropostasController::class, 'index']);
    Route::post('/propostas/{proposal}/aceitar', [PrestadorPropostasController::class, 'aceitar']);
    Route::post('/propostas/{proposal}/recusar', [PrestadorPropostasController::class, 'recusar']);
    Route::delete('/propostas/{proposal}/cancelar', [PrestadorPropostasController::class, 'cancelar']);

    Route::get('/agenda', [PrestadorAgendaController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'api.company'])->prefix('empresa')->group(function () {
    Route::get('/dashboard', [EmpresaDashboardController::class, 'index']);
    Route::post('/propostas/{proposal}/aceitar', [EmpresaPropostasController::class, 'aceitar']);
    Route::post('/propostas/{proposal}/rejeitar', [EmpresaPropostasController::class, 'rejeitar']);
});
