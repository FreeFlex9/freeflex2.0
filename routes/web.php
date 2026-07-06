<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Company;
use App\Http\Controllers\Provider;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// ─── Área Empresa ─────────────────────────────────────────────────────────────
Route::prefix('empresa')->name('empresa.')->group(function () {

    Route::middleware('guest:company')->group(function () {
        Route::get('/login',       [Company\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login',      [Company\AuthController::class, 'login'])->name('login.submit');
        Route::get('/cadastro',    [Company\AuthController::class, 'showRegister'])->name('register');
        Route::post('/cadastro',   [Company\AuthController::class, 'register'])->name('register.submit');
    });

    Route::post('/logout', [Company\AuthController::class, 'logout'])->name('logout');

    Route::middleware('is_company')->group(function () {
        Route::get('/dashboard', [Company\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/demandas',                    [Company\DemandasController::class, 'index'])->name('demandas.index');
        Route::get('/demandas/nova',               [Company\DemandasController::class, 'create'])->name('demandas.create');
        Route::post('/demandas',                   [Company\DemandasController::class, 'store'])->name('demandas.store');
        Route::get('/demandas/{demand}',           [Company\DemandasController::class, 'show'])->name('demandas.show');
        Route::get('/demandas/{demand}/editar',    [Company\DemandasController::class, 'edit'])->name('demandas.edit');
        Route::put('/demandas/{demand}',           [Company\DemandasController::class, 'update'])->name('demandas.update');
        Route::delete('/demandas/{demand}',        [Company\DemandasController::class, 'destroy'])->name('demandas.destroy');

        Route::post('/propostas/{proposal}/aceitar',    [Company\PropostasController::class, 'aceitar'])->name('propostas.aceitar');
        Route::post('/propostas/{proposal}/rejeitar',   [Company\PropostasController::class, 'rejeitar'])->name('propostas.rejeitar');
        Route::get('/propostas/{proposal}/mensagens',   [Company\PropostasController::class, 'mensagens'])->name('propostas.mensagens');
        Route::post('/propostas/{proposal}/mensagens',  [Company\PropostasController::class, 'enviarMensagem'])->name('propostas.mensagens.enviar');

        Route::get('/prestadores',                         [Company\BuscaPrestadoresController::class, 'index'])->name('prestadores.index');
        Route::post('/prestadores/{provider}/contratar',  [Company\ContratarController::class, 'store'])->name('prestadores.contratar');

        Route::get('/avaliacoes',          [Company\AvaliacoesController::class, 'index'])->name('avaliacoes.index');
        Route::post('/avaliacoes',         [Company\AvaliacoesController::class, 'store'])->name('avaliacoes.store');

        Route::get('/perfil',            [Company\PerfilController::class, 'index'])->name('perfil');
        Route::put('/perfil',            [Company\PerfilController::class, 'updateInfo'])->name('perfil.update');
        Route::put('/perfil/senha',      [Company\PerfilController::class, 'updatePassword'])->name('perfil.senha');
        Route::post('/perfil/documento', [Company\PerfilController::class, 'uploadDocument'])->name('perfil.documento');
    });
});

// ─── Área Prestador ───────────────────────────────────────────────────────────
Route::prefix('prestador')->name('prestador.')->group(function () {

    Route::middleware('guest:provider')->group(function () {
        Route::get('/login',     [Provider\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [Provider\AuthController::class, 'login'])->name('login.submit');
        Route::get('/cadastro',  [Provider\AuthController::class, 'showRegister'])->name('register');
        Route::post('/cadastro', [Provider\AuthController::class, 'register'])->name('register.submit');
    });

    Route::post('/logout', [Provider\AuthController::class, 'logout'])->name('logout');

    Route::middleware('is_provider')->group(function () {
        Route::get('/dashboard',           [Provider\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/agenda',              [Provider\AgendaController::class, 'index'])->name('agenda.index');
        Route::get('/avaliacoes',          [Provider\AvaliacoesController::class, 'index'])->name('avaliacoes.index');
        Route::get('/servicos',            [Provider\MeusServicosController::class, 'index'])->name('servicos.index');
        Route::post('/servicos/toggle',    [Provider\MeusServicosController::class, 'toggle'])->name('servicos.toggle');
        Route::get('/demandas',            [Provider\DemandasController::class, 'index'])->name('demandas.index');
        Route::post('/demandas/proposta',  [Provider\DemandasController::class, 'enviarProposta'])->name('demandas.proposta');

        Route::get('/propostas',                             [Provider\PropostasController::class, 'index'])->name('propostas.index');
        Route::post('/propostas/{proposal}/aceitar',         [Provider\PropostasController::class, 'aceitar'])->name('propostas.aceitar');
        Route::post('/propostas/{proposal}/recusar',         [Provider\PropostasController::class, 'recusar'])->name('propostas.recusar');
        Route::delete('/propostas/{proposal}/cancelar',      [Provider\PropostasController::class, 'cancelar'])->name('propostas.cancelar');
        Route::get('/propostas/{proposal}/mensagens',        [Provider\PropostasController::class, 'mensagens'])->name('propostas.mensagens');
        Route::post('/propostas/{proposal}/mensagens',       [Provider\PropostasController::class, 'enviarMensagem'])->name('propostas.mensagens.enviar');
        Route::get('/perfil',              [Provider\PerfilController::class, 'index'])->name('perfil');
        Route::put('/perfil',              [Provider\PerfilController::class, 'updateInfo'])->name('perfil.update');
        Route::put('/perfil/senha',        [Provider\PerfilController::class, 'updatePassword'])->name('perfil.senha');
        Route::put('/perfil/endereco',      [Provider\PerfilController::class, 'updateAddress'])->name('perfil.endereco');
        Route::post('/perfil/documento',   [Provider\PerfilController::class, 'uploadDocument'])->name('perfil.documento');
        Route::delete('/perfil/documento', [Provider\PerfilController::class, 'removeDocument'])->name('perfil.documento.remove');
    });
});

// ─── Área Admin ───────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login',  [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.submit');
    });

    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('is_admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/empresas', [Admin\EmpresasController::class, 'index'])->name('empresas.index');
        Route::post('/empresas/{empresa}/aprovar', [Admin\EmpresasController::class, 'aprovar'])->name('empresas.aprovar');
        Route::post('/empresas/{empresa}/rejeitar', [Admin\EmpresasController::class, 'rejeitar'])->name('empresas.rejeitar');

        Route::get('/prestadores', [Admin\PrestadoresController::class, 'index'])->name('prestadores.index');
        Route::post('/prestadores/{prestador}/aprovar', [Admin\PrestadoresController::class, 'aprovar'])->name('prestadores.aprovar');
        Route::post('/prestadores/{prestador}/rejeitar', [Admin\PrestadoresController::class, 'rejeitar'])->name('prestadores.rejeitar');
        Route::post('/prestadores/{prestador}/aprovar-cnh', [Admin\PrestadoresController::class, 'aprovarCnh'])->name('prestadores.aprovarCnh');
        Route::post('/prestadores/{prestador}/rejeitar-cnh', [Admin\PrestadoresController::class, 'rejeitarCnh'])->name('prestadores.rejeitarCnh');

        Route::get('/usuarios', [Admin\UsuariosController::class, 'index'])->name('usuarios.index');
        Route::delete('/usuarios/{tipo}/{id}', [Admin\UsuariosController::class, 'destroy'])
            ->where('tipo', 'prestador|empresa')
            ->where('id', '[0-9]+')
            ->name('usuarios.destroy');

        Route::get('/demandas', [Admin\DemandasController::class, 'index'])->name('demandas.index');
        Route::get('/demandas/{demanda}/propostas', [Admin\DemandasController::class, 'propostas'])->name('demandas.propostas');
        Route::post('/demandas/propostas/{proposta}/aprovar', [Admin\DemandasController::class, 'aprovarProposta'])->name('demandas.propostas.aprovar');
        Route::post('/demandas/propostas/{proposta}/rejeitar', [Admin\DemandasController::class, 'rejeitarProposta'])->name('demandas.propostas.rejeitar');

        Route::get('/servicos', [Admin\ServicosController::class, 'index'])->name('servicos.index');

        Route::get('/exportacoes', [Admin\ExportController::class, 'page'])->name('exportacoes');
        Route::get('/exportar/prestadores', [Admin\ExportController::class, 'prestadores'])->name('exportar.prestadores');
        Route::get('/exportar/empresas',    [Admin\ExportController::class, 'empresas'])->name('exportar.empresas');
        Route::post('/servicos', [Admin\ServicosController::class, 'store'])->name('servicos.store');
        Route::put('/servicos/{servico}', [Admin\ServicosController::class, 'update'])->name('servicos.update');
        Route::delete('/servicos/{servico}', [Admin\ServicosController::class, 'destroy'])->name('servicos.destroy');

        Route::get('/perfil', [Admin\PerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [Admin\PerfilController::class, 'update'])->name('perfil.update');

        Route::get('/config-email', [Admin\ConfigEmailController::class, 'index'])->name('config-email.index');
        Route::put('/config-email', [Admin\ConfigEmailController::class, 'salvar'])->name('config-email.update');
    });
});
