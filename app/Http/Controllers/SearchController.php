<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** ======================================================
     *  🔍 Buscar estudiantes por nombre, teléfono o nivel
     * ====================================================== */
    public function students(Request $request)
    {
        $q = trim($request->input('q'));

        if (!$q) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar un texto de búsqueda.',
                'count'   => 0,
                'data'    => []
            ], 400);
        }

        $students = Student::with(['branch', 'user'])
            ->where(function ($query) use ($q) {
                $query->where('nombres', 'like', "%$q%")
                    ->orWhere('telefono', 'like', "%$q%")
                    ->orWhere('grade', 'like', "%$q%")
                    ->orWhere('level', 'like', "%$q%");
            })
            ->orderBy('nombres')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => "🔍 Resultados de búsqueda para: '$q'",
            'count'   => $students->count(),
            'data'    => $students
        ]);
    }

    /** ======================================================
     *  📘 Buscar inscripciones por estudiante o curso
     * ====================================================== */
    public function enrollments(Request $request)
    {
        $q = trim($request->input('q'));

        if (!$q) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar un texto de búsqueda.',
                'count'   => 0,
                'data'    => []
            ], 400);
        }

        $enrollments = Enrollment::with(['student.user', 'offering.course', 'offering.teacher.user', 'offering.branch'])
            ->where(function ($query) use ($q) {
                $query->whereHas('student', fn($s) => $s->where('nombres', 'like', "%$q%"))
                    ->orWhereHas('offering.course', fn($c) => $c->where('nombre', 'like', "%$q%"))
                    ->orWhereHas('offering.teacher.user', fn($t) => $t->where('name', 'like', "%$q%"));
            })
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => "📘 Resultados de búsqueda para: '$q'",
            'count'   => $enrollments->count(),
            'data'    => $enrollments
        ]);
    }
}
