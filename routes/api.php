<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\StudentController;

Route::prefix('v1')->group(function () {
    // Público
    Route::get('/ping', fn () => response()->json([
        'ok'   => true,
        'time' => now()->toDateTimeString(),
    ]));

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login',    [AuthController::class, 'login'])->name('auth.login');

    // Protegido
    Route::middleware('auth:api')->group(function () {
        Route::get('/auth/me',      [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::apiResource('branches', BranchController::class)->names('branches');
        Route::apiResource('students', StudentController::class)->names('students');
    });
});
