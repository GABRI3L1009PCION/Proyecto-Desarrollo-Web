<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

// Redirige raíz hacia login
Route::get('/', function () {
    return redirect()->route('login.view');
});

// =========================
//  VISTAS DE AUTENTICACIÓN
// =========================
Route::view('/login', 'auth.login')->name('login.view');
Route::view('/register', 'auth.register')->name('register.view');

// =========================
//  DASHBOARD / PANELES
// =========================
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Panel de administrador
Route::get('/admin', [AdminPanelController::class, 'index'])->name('admin.panel');

// =========================
//  LOGOUT
// =========================
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login.view');
})->name('logout');
