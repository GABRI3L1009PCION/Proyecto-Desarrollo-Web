<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** Buscar estudiantes por nombre, teléfono o nivel */
    public function students(Request $request)
    {
        $q = $request->input('q');

        $students = Student::with('branch')
            ->when($q, function ($query, $q) {
                $query->where('nombres', 'like', "%$q%")
                    ->orWhere('telefono', 'like', "%$q%")
                    ->orWhere('grade', 'like', "%$q%")
                    ->orWhere('level', 'like', "%$q%");
            })
            ->limit(20)
            ->get();

        return response()->json($students);
    }

    /** Buscar inscripciones por estudiante o curso */
    public function enrollments(Request $request)
    {
        $q = $request->input('q');

        $enrollments = Enrollment::with(['student', 'offering.course'])
            ->when($q, function ($query, $q) {
                $query->whereHas('student', fn($s) => $s->where('nombres', 'like', "%$q%"))
                    ->orWhereHas('offering.course', fn($c) => $c->where('nombre', 'like', "%$q%"));
            })
            ->limit(20)
            ->get();

        return response()->json($enrollments);
    }
}
