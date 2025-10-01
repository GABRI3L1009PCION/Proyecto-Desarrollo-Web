<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OfferingController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;

Route::prefix('v1')->group(function () {
    // --------------------
    // Público
    // --------------------
    Route::get('/ping', fn () => response()->json([
        'ok'   => true,
        'time' => now()->toDateTimeString(),
    ]));

    // --------------------
    // Autenticación
    // --------------------
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login',    [AuthController::class, 'login'])->name('auth.login');

    // --------------------
    // Protegido con Passport
    // --------------------
    Route::middleware('auth:api')->group(function () {
        Route::get('/auth/me',      [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // --------------------
        // Sucursales
        // --------------------
        Route::apiResource('branches', BranchController::class);

        // --------------------
        // Alumnos
        // --------------------
        Route::apiResource('students', StudentController::class);

        // --------------------
        // Catedráticos
        // --------------------
        Route::apiResource('teachers', TeacherController::class);

        // --------------------
        // Cursos y Ofertas
        // --------------------
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('offerings', OfferingController::class);

        // --------------------
        // Inscripciones y Notas
        // --------------------
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::apiResource('grades', GradeController::class);

        // --------------------
        // Reportes
        // --------------------
        Route::prefix('reports')->group(function () {
            Route::get('students/by-date',  [ReportController::class, 'studentsByDate']);
            Route::get('students/by-grade', [ReportController::class, 'studentsByGrade']);
            Route::get('grades/by-course',  [ReportController::class, 'gradesByCourse']);

            // Exportaciones a Excel
            Route::get('export/students', [ReportController::class, 'exportStudents']);
            Route::get('export/grades',   [ReportController::class, 'exportGrades']);
        });

        // --------------------
        // Búsqueda
        // --------------------
        Route::prefix('search')->group(function () {
            Route::get('students',    [SearchController::class, 'students']);
            Route::get('enrollments', [SearchController::class, 'enrollments']);
        });
    });
});
