<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\EmpresaAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// Cadastro de empresa
Route::middleware('guest')->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/cadastro', [EmpresaAuthController::class, 'showRegister'])->name('register');
    Route::post('/cadastro', [EmpresaAuthController::class, 'register'])->name('register.submit');
});

// Dashboard usuário comum (Jetstream)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// ─── Área Admin ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Rotas públicas (login/logout)
    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    // Rotas protegidas
    Route::middleware('is_admin')->group(function () {

        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Aprovações de empresas
        Route::get('/empresas', [Admin\EmpresasController::class, 'index'])->name('empresas.index');
        Route::post('/empresas/{empresa}/aprovar', [Admin\EmpresasController::class, 'aprovar'])->name('empresas.aprovar');
        Route::post('/empresas/{empresa}/rejeitar', [Admin\EmpresasController::class, 'rejeitar'])->name('empresas.rejeitar');

        // Aprovações de prestadores
        Route::get('/prestadores', [Admin\PrestadoresController::class, 'index'])->name('prestadores.index');
        Route::post('/prestadores/{prestador}/aprovar', [Admin\PrestadoresController::class, 'aprovar'])->name('prestadores.aprovar');
        Route::post('/prestadores/{prestador}/rejeitar', [Admin\PrestadoresController::class, 'rejeitar'])->name('prestadores.rejeitar');

        // Demandas
        Route::get('/demandas', [Admin\DemandasController::class, 'index'])->name('demandas.index');
        Route::get('/demandas/{demanda}/propostas', [Admin\DemandasController::class, 'propostas'])->name('demandas.propostas');
        Route::post('/demandas/propostas/{proposta}/aprovar', [Admin\DemandasController::class, 'aprovarProposta'])->name('demandas.propostas.aprovar');
        Route::post('/demandas/propostas/{proposta}/rejeitar', [Admin\DemandasController::class, 'rejeitarProposta'])->name('demandas.propostas.rejeitar');

        // Serviços
        Route::get('/servicos', [Admin\ServicosController::class, 'index'])->name('servicos.index');
        Route::post('/servicos', [Admin\ServicosController::class, 'store'])->name('servicos.store');
        Route::put('/servicos/{servico}', [Admin\ServicosController::class, 'update'])->name('servicos.update');
        Route::delete('/servicos/{servico}', [Admin\ServicosController::class, 'destroy'])->name('servicos.destroy');

        // Perfil
        Route::get('/perfil', [Admin\PerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [Admin\PerfilController::class, 'update'])->name('perfil.update');

        // Config e-mail
        Route::get('/config-email', [Admin\ConfigEmailController::class, 'index'])->name('config-email.index');
        Route::put('/config-email', [Admin\ConfigEmailController::class, 'salvar'])->name('config-email.update');
    });
});
