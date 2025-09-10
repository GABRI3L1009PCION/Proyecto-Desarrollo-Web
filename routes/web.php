<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminPanelController;

Route::get('/', fn () => redirect()->route('admin.panel'));

Route::get('/admin', [AdminPanelController::class, 'index'])
    // ->middleware('auth') // actívalo si luego tendrás login web
    ->name('admin.panel');

// Ruta de logout para que exista route('logout')
Route::post('/logout', function () {
    // Si no usas auth web aún, no pasa nada por llamarlo
    Auth::guard('web')->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('admin.panel');
})->name('logout');
