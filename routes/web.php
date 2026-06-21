<?php

use App\Http\Controllers\EmpresaAuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// Cadastro de empresa (login unificado via Fortify em /login)
Route::middleware('guest')->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/cadastro', [EmpresaAuthController::class, 'showRegister'])->name('register');
    Route::post('/cadastro', [EmpresaAuthController::class, 'register'])->name('register.submit');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
