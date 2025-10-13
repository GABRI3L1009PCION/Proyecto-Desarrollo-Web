<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class SecretariaPanelController extends Controller
{
    public function index()
    {
        // 🔹 Totales reales
        $totalAlumnos = Student::count();
        $totalCatedraticos = Teacher::count();
        $totalCursos = Course::count();
        $totalInscripciones = Enrollment::count();

        // 🔹 Últimas inscripciones con relaciones (si existen)
        $ultimasInscripciones = Enrollment::with(['student', 'course', 'branch'])
            ->latest()
            ->take(5)
            ->get();

        return view('Secretaria.sec_panel', compact(
            'totalAlumnos',
            'totalCatedraticos',
            'totalCursos',
            'totalInscripciones',
            'ultimasInscripciones'
        ));
    }
}
