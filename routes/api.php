<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\DocumentosController as AdminDocumentosController;
use App\Http\Controllers\Api\Admin\EmpresasController as AdminEmpresasController;
use App\Http\Controllers\Api\Admin\PrestadoresController as AdminPrestadoresController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Empresa\DashboardController as EmpresaDashboardController;
use App\Http\Controllers\Api\Empresa\MensagensController as EmpresaMensagensController;
use App\Http\Controllers\Api\Empresa\PropostasController as EmpresaPropostasController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\Prestador\AgendaController as PrestadorAgendaController;
use App\Http\Controllers\Api\Prestador\DashboardController as PrestadorDashboardController;
use App\Http\Controllers\Api\Prestador\DemandasController as PrestadorDemandasController;
use App\Http\Controllers\Api\Prestador\MensagensController as PrestadorMensagensController;
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
    Route::post('/propostas/{proposal}/desistir', [PrestadorPropostasController::class, 'desistir']);

    Route::get('/agenda', [PrestadorAgendaController::class, 'index']);

    Route::get('/mensagens', [PrestadorMensagensController::class, 'index']);
    Route::get('/mensagens/nao-lidas', [PrestadorMensagensController::class, 'unreadCount']);
    Route::get('/mensagens/{proposal}', [PrestadorMensagensController::class, 'show']);
    Route::post('/mensagens/{proposal}', [PrestadorMensagensController::class, 'store']);
    Route::post('/mensagens/{proposal}/lida', [PrestadorMensagensController::class, 'markRead']);
});

Route::middleware(['auth:sanctum', 'api.company'])->prefix('empresa')->group(function () {
    Route::get('/dashboard', [EmpresaDashboardController::class, 'index']);
    Route::post('/propostas/{proposal}/aceitar', [EmpresaPropostasController::class, 'aceitar']);
    Route::post('/propostas/{proposal}/rejeitar', [EmpresaPropostasController::class, 'rejeitar']);

    Route::get('/mensagens', [EmpresaMensagensController::class, 'index']);
    Route::get('/mensagens/nao-lidas', [EmpresaMensagensController::class, 'unreadCount']);
    Route::get('/mensagens/{demand}', [EmpresaMensagensController::class, 'show']);
    Route::post('/mensagens/{demand}', [EmpresaMensagensController::class, 'store']);
    Route::post('/mensagens/{demand}/lida', [EmpresaMensagensController::class, 'markRead']);
});

Route::middleware(['auth:sanctum', 'api.admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    Route::get('/empresas', [AdminEmpresasController::class, 'index']);
    Route::post('/empresas/{empresa}/aprovar', [AdminEmpresasController::class, 'aprovar']);
    Route::post('/empresas/{empresa}/rejeitar', [AdminEmpresasController::class, 'rejeitar']);

    Route::get('/prestadores', [AdminPrestadoresController::class, 'index']);
    Route::post('/prestadores/{prestador}/aprovar', [AdminPrestadoresController::class, 'aprovar']);
    Route::post('/prestadores/{prestador}/rejeitar', [AdminPrestadoresController::class, 'rejeitar']);
    Route::post('/prestadores/{prestador}/aprovar-cnh', [AdminPrestadoresController::class, 'aprovarCnh']);
    Route::post('/prestadores/{prestador}/rejeitar-cnh', [AdminPrestadoresController::class, 'rejeitarCnh']);

    Route::get('/documentos/{tipo}/{id}/{campo}', [AdminDocumentosController::class, 'show'])
        ->where('tipo', 'prestador|empresa')
        ->where('id', '[0-9]+');
});
