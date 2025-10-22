<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\SucursalAdminController;
use App\Http\Controllers\AlumnoAdminController;
use App\Http\Controllers\AdminCursosController;
use App\Http\Controllers\AdminTeacherController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecretariaPanelController;
use App\Http\Controllers\SecretariaAlumnoController;
use App\Http\Controllers\EstudiantePanelController;
use App\Http\Controllers\AdministradorReportesController;


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

    // ⚙ ASIGNACIÓN DE CURSOS A CATEDRÁTICOS
    Route::post('/catedraticos/{id}/asignar-curso', [AdminTeacherController::class, 'asignarCurso'])
        ->name('administrador.catedraticos.asignar');

    // 📚 VER CURSOS (para el modal, solo lectura)
    Route::get('/catedraticos/{id}/cursos', [AdminTeacherController::class, 'getCursos'])
        ->name('administrador.catedraticos.cursos');

    // ✏ ACTUALIZAR ASIGNACIÓN DE CURSO (versión web)
    Route::put('/asignacion/{id}', [AdminTeacherController::class, 'actualizarAsignacion'])
        ->name('administrador.asignacion.update');

    // 🗑 ELIMINAR ASIGNACIÓN DE CURSO (versión web)
    Route::delete('/asignacion/{id}', [AdminTeacherController::class, 'eliminarAsignacion'])
        ->name('administrador.asignacion.destroy');

    // 📘 CURSOS
    Route::get('/cursos', [AdminCursosController::class, 'index'])->name('administrador.cursos');
    Route::post('/cursos', [AdminCursosController::class, 'store'])->name('administrador.cursos.store');
    Route::put('/cursos/{id}', [AdminCursosController::class, 'update'])->name('administrador.cursos.update');
    Route::delete('/cursos/{id}', [AdminCursosController::class, 'destroy'])->name('administrador.cursos.destroy');

// 📊 REPORTES — Panel del Administrador
    Route::get('/reportes', [AdministradorReportesController::class, 'index'])
        ->name('administrador.reportes');

// ✅ Endpoints AJAX de reportes (para el botón “Generar”)
    Route::get('/reportes/inscritos', [AdministradorReportesController::class, 'inscritos'])
        ->name('administrador.reportes.inscritos');

    Route::get('/reportes/grado-nivel', [AdministradorReportesController::class, 'gradoNivel'])
        ->name('administrador.reportes.gradoNivel');

    Route::get('/reportes/notas', [AdministradorReportesController::class, 'notas'])
        ->name('administrador.reportes.notas');

    // ✅ EXPORTAR A EXCEL
    Route::get('/reportes/exportar/inscritos', [AdministradorReportesController::class, 'exportarInscritos'])->name('administrador.reportes.export.inscritos');
    Route::get('/reportes/exportar/grado-nivel', [AdministradorReportesController::class, 'exportarGradoNivel'])->name('administrador.reportes.export.gradoNivel');
    Route::get('/reportes/exportar/notas', [AdministradorReportesController::class, 'exportarNotas'])->name('administrador.reportes.export.notas');
});

// ===== CATEDRÁTICO =====
Route::middleware(['auth', 'role:catedratico'])
    ->prefix('catedratico')
    ->group(function () {

        // 🏠 PANEL PRINCIPAL
        Route::get('/panel', [App\Http\Controllers\CatedraticoPanelController::class, 'index'])
            ->name('catedratico.panel');

        // 👨‍🎓 Ver alumnos (modal desde el panel principal)
        Route::get('/curso/{id}/alumnos', [App\Http\Controllers\CatedraticoCursosController::class, 'alumnos'])
            ->name('catedratico.curso.alumnos');

        // 📘 Módulo “Mis Cursos”
        Route::get('/mis-cursos', [App\Http\Controllers\CatedraticoCursosController::class, 'index'])
            ->name('catedratico.cursos');

        // ======================================================
        // 🎓 MÓDULO DE CALIFICACIONES
        // ======================================================
        Route::get('/calificaciones', [App\Http\Controllers\CatedraticoCalificacionesController::class, 'index'])
            ->name('catedratico.calificaciones');

        Route::post('/calificaciones/guardar', [App\Http\Controllers\CatedraticoCalificacionesController::class, 'guardar'])
            ->name('catedratico.calificaciones.guardar');
    });


// ===== ESTUDIANTE =====
Route::middleware(['auth', 'role:estudiante'])
    ->prefix('estudiante')
    ->group(function () {

        // 🏠 PANEL PRINCIPAL
        Route::get('/panel', [EstudiantePanelController::class, 'index'])
            ->name('estudiante.panel');

        // 📘 CURSOS (con notas dentro)
        Route::get('/cursos', [EstudiantePanelController::class, 'misCursos'])
            ->name('estudiante.cursos');

        // 📊 DESEMPEÑO ACADÉMICO
        Route::get('/desempeno', [EstudiantePanelController::class, 'miDesempeno'])
            ->name('estudiante.desempeno');

        // 👤 PERFIL PERSONAL
        Route::get('/perfil', [EstudiantePanelController::class, 'perfil'])
            ->name('estudiante.perfil');
    });


// ===== SECRETARÍA =====
Route::middleware(['auth', 'role:secretaria'])->prefix('secretaria')->group(function () {

    // 🏠 PANEL PRINCIPAL
    Route::get('/panel', [SecretariaPanelController::class, 'index'])->name('secretaria.panel');

    // 🎓 ALUMNOS
    Route::get('/alumnos', [SecretariaAlumnoController::class, 'index'])->name('secretaria.alumnos');
    Route::post('/alumnos', [SecretariaAlumnoController::class, 'store'])->name('secretaria.alumnos.store');
    Route::put('/alumnos/{id}', [SecretariaAlumnoController::class, 'update'])->name('secretaria.alumnos.update');
    Route::delete('/alumnos/{id}', [SecretariaAlumnoController::class, 'destroy'])->name('secretaria.alumnos.destroy');

    // 🧾 INSCRIPCIONES
    Route::get('/inscripciones', [App\Http\Controllers\SecretariaInscripcionController::class, 'index'])
        ->name('secretaria.inscripciones');
    Route::post('/inscripciones', [App\Http\Controllers\SecretariaInscripcionController::class, 'store'])
        ->name('secretaria.inscripciones.store');
    Route::put('/inscripciones/{id}', [App\Http\Controllers\SecretariaInscripcionController::class, 'update'])
        ->name('secretaria.inscripciones.update');
    Route::delete('/inscripciones/{id}', [App\Http\Controllers\SecretariaInscripcionController::class, 'destroy'])
        ->name('secretaria.inscripciones.destroy');

    // 👩‍🏫 CATEDRÁTICOS
    Route::get('/catedraticos', [App\Http\Controllers\SecretariaCatedraticoController::class, 'index'])
        ->name('secretaria.catedraticos');
    Route::post('/catedraticos', [App\Http\Controllers\SecretariaCatedraticoController::class, 'store'])
        ->name('secretaria.catedraticos.store');
    Route::put('/catedraticos/{id}', [App\Http\Controllers\SecretariaCatedraticoController::class, 'update'])
        ->name('secretaria.catedraticos.update');
    Route::delete('/catedraticos/{id}', [App\Http\Controllers\SecretariaCatedraticoController::class, 'destroy'])
        ->name('secretaria.catedraticos.destroy');

    // 📚 Ver cursos asignados (solo lectura)
    Route::get('/catedraticos/{id}/cursos', [App\Http\Controllers\SecretariaCatedraticoController::class, 'cursos'])
        ->name('secretaria.catedraticos.cursos');

    // 📊 REPORTES
    Route::get('/reportes', [App\Http\Controllers\SecretariaReportController::class, 'index'])
        ->name('secretaria.reportes');
});

// 🚪 Cierre de sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
