<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SucursalAdminController;
use App\Http\Controllers\AlumnoAdminController;
use App\Http\Controllers\AdminCursosController;
use App\Http\Controllers\AdminTeacherController; // ✅ Controlador de catedráticos
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecretariaPanelController;
use App\Http\Controllers\SecretariaAlumnoController;


/*
|--------------------------------------------------------------------------
| RUTAS WEB — INTERFAZ LARAVEL
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'))->name('welcome');

// =========================
// 🔐 AUTENTICACIÓN (vistas)
// =========================
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.submit');
Route::view('/register', 'auth.register')->name('register.view');

// =========================
// 📊 DASHBOARDS POR ROL
// =========================

// ===== ADMINISTRADOR =====
Route::middleware(['auth', 'role:admin'])->prefix('administrador')->group(function () {

    // 🏠 PANEL PRINCIPAL
    Route::get('/panel', [AdminPanelController::class, 'index'])->name('administrador.panel');

    // 👥 USUARIOS
    Route::get('/usuarios', [AdminUserController::class, 'index'])->name('administrador.usuarios');
    Route::post('/usuarios', [AdminUserController::class, 'store'])->name('administrador.usuarios.store');
    Route::put('/usuarios/{id}', [AdminUserController::class, 'update'])->name('administrador.usuarios.update');
    Route::delete('/usuarios/{id}', [AdminUserController::class, 'destroy'])->name('administrador.usuarios.destroy');

    // 🏢 SUCURSALES
    Route::get('/sucursales', [SucursalAdminController::class, 'index'])->name('administrador.sucursales');
    Route::post('/sucursales', [SucursalAdminController::class, 'store'])->name('administrador.sucursales.store');
    Route::put('/sucursales/{id}', [SucursalAdminController::class, 'update'])->name('administrador.sucursales.update');
    Route::delete('/sucursales/{id}', [SucursalAdminController::class, 'destroy'])->name('administrador.sucursales.destroy');

    // 🎓 ALUMNOS
    Route::get('/alumnos', [AlumnoAdminController::class, 'index'])->name('administrador.alumnos');
    Route::post('/alumnos', [AlumnoAdminController::class, 'store'])->name('administrador.alumnos.store');
    Route::put('/alumnos/{id}', [AlumnoAdminController::class, 'update'])->name('administrador.alumnos.update');
    Route::delete('/alumnos/{id}', [AlumnoAdminController::class, 'destroy'])->name('administrador.alumnos.destroy');

    // 👨‍🏫 CATEDRÁTICOS
    Route::get('/catedraticos', [AdminTeacherController::class, 'index'])->name('administrador.catedraticos');
    Route::post('/catedraticos', [AdminTeacherController::class, 'store'])->name('administrador.catedraticos.store');
    Route::put('/catedraticos/{id}', [AdminTeacherController::class, 'update'])->name('administrador.catedraticos.update');
    Route::delete('/catedraticos/{id}', [AdminTeacherController::class, 'destroy'])->name('administrador.catedraticos.destroy');

    // ⚙️ ASIGNACIÓN DE CURSOS A CATEDRÁTICOS
    Route::post('/catedraticos/{id}/asignar-curso', [AdminTeacherController::class, 'asignarCurso'])
        ->name('administrador.catedraticos.asignar');

    // 📚 VER CURSOS (para el modal, solo lectura)
    Route::get('/catedraticos/{id}/cursos', [AdminTeacherController::class, 'getCursos'])
        ->name('administrador.catedraticos.cursos');

    // ✏️ ACTUALIZAR ASIGNACIÓN DE CURSO (versión web)
    Route::put('/asignacion/{id}', [AdminTeacherController::class, 'actualizarAsignacion'])
        ->name('administrador.asignacion.update');

    // 🗑️ ELIMINAR ASIGNACIÓN DE CURSO (versión web)
    Route::delete('/asignacion/{id}', [AdminTeacherController::class, 'eliminarAsignacion'])
        ->name('administrador.asignacion.destroy');

    // 📘 CURSOS
    Route::get('/cursos', [AdminCursosController::class, 'index'])->name('administrador.cursos');
    Route::post('/cursos', [AdminCursosController::class, 'store'])->name('administrador.cursos.store');
    Route::put('/cursos/{id}', [AdminCursosController::class, 'update'])->name('administrador.cursos.update');
    Route::delete('/cursos/{id}', [AdminCursosController::class, 'destroy'])->name('administrador.cursos.destroy');

    // 📊 REPORTES
    Route::get('/reportes', [ReportController::class, 'index'])->name('administrador.reportes');
});

// ===== CATEDRÁTICO =====
Route::middleware(['auth', 'role:catedratico'])->group(function () {
    Route::get('/catedratico/panel', fn() => view('Catedratico.panel'))->name('catedratico.panel');
});

// ===== ESTUDIANTE =====
Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/estudiante/panel', fn() => view('Estudiante.panel'))->name('estudiante.panel');
});

// ===== SECRETARIA =====
Route::middleware(['auth', 'role:secretaria'])->prefix('secretaria')->group(function () {

    // 🏠 PANEL PRINCIPAL
    Route::get('/panel', [SecretariaPanelController::class, 'index'])->name('secretaria.panel');

    // 🎓 ALUMNOS
    Route::get('/alumnos', [SecretariaAlumnoController::class, 'index'])->name('secretaria.alumnos');
    Route::post('/alumnos', [SecretariaAlumnoController::class, 'store'])->name('secretaria.alumnos.store');
    Route::put('/alumnos/{id}', [SecretariaAlumnoController::class, 'update'])->name('secretaria.alumnos.update');
    Route::delete('/alumnos/{id}', [SecretariaAlumnoController::class, 'destroy'])->name('secretaria.alumnos.destroy');

    // 🧾 INSCRIPCIONES
    //Route::get('/inscripciones', [App\Http\Controllers\SecretariaInscripcionController::class, 'index'])->name('secretaria.inscripciones');

    // 👩‍🏫 CATEDRÁTICOS
    Route::get('/catedraticos', [App\Http\Controllers\SecretariaCatedraticoController::class, 'index'])->name('secretaria.catedraticos');

    // 📊 REPORTES
    Route::get('/reportes', [App\Http\Controllers\SecretariaReportController::class, 'index'])->name('secretaria.reportes');
});


// 🚪 Cierre de sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
