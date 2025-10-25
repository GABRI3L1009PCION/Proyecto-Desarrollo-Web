<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /** ======================================================
     *  📋 Listar todos los estudiantes (con búsqueda, filtro y orden)
     * ====================================================== */
    public function index(Request $request)
    {
        $query = Student::with(['branch', 'user', 'enrollments.offering.course']);

        // 🔍 Búsqueda general (nombre, teléfono o correo)
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombres', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('email', 'like', "%{$q}%");
                    });
            });
        }

        // 🏫 Filtro por sucursal
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // 🎓 Filtro por grado (Novatos / Expertos)
        if ($grade = $request->input('grade')) {
            $query->where('grade', $grade);
        }

        // 📘 Filtro por nivel (Principiantes / Avanzados)
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        // 🔄 Ordenamiento
        switch ($request->input('sort', 'recientes')) {
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('nombres', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 📄 Paginación (15 por página)
        $students = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => '📋 Lista de estudiantes obtenida correctamente.',
            'count'   => $students->total(),
            'data'    => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page'    => $students->lastPage(),
                'per_page'     => $students->perPage(),
            ]
        ], Response::HTTP_OK);
    }

    /** ======================================================
     *  🔍 Usuarios con rol "estudiante" sin vincular a Student
     * ====================================================== */
    public function unlinkedUsers()
    {
        $users = User::where('role', 'estudiante')
            ->whereDoesntHave('student')
            ->select('id', 'name', 'email')
            ->orderBy('email')
            ->get();

        return response()->json($users, Response::HTTP_OK);
    }

    /** ======================================================
     *  ➕ Registrar un nuevo estudiante
     * ====================================================== */
    public function store(Request $r)
    {
        $data = $r->validate([
            'user_id'          => 'nullable|exists:users,id|unique:students,user_id',
            'branch_id'        => 'required|exists:branches,id',
            'nombres'          => 'required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'required|in:Novatos,Expertos',
            'level'            => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $student = Student::create($data);

        return response()->json([
            'success' => true,
            'message' => '✅ Estudiante registrado correctamente.',
            'data'    => $student->load(['branch', 'user'])
        ], Response::HTTP_CREATED);
    }

    /** ======================================================
     *  👀 Ver detalles de un estudiante
     * ====================================================== */
    public function show(Student $student)
    {
        return response()->json([
            'success' => true,
            'message' => '👀 Detalles del estudiante obtenidos.',
            'data'    => $student->load(['branch', 'user', 'enrollments.offering.course'])
        ]);
    }

    /** ======================================================
     *  ✏️ Actualizar información de un estudiante
     * ====================================================== */
    public function update(Request $r, Student $student)
    {
        $data = $r->validate([
            'branch_id'        => 'sometimes|required|exists:branches,id',
            'nombres'          => 'sometimes|required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'sometimes|required|in:Novatos,Expertos',
            'level'            => 'sometimes|required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => '✏️ Datos del estudiante actualizados correctamente.',
            'data'    => $student->load(['branch', 'user'])
        ], Response::HTTP_OK);
    }

    /** ======================================================
     *  ❌ Eliminar estudiante (solo si no tiene inscripciones)
     * ====================================================== */
    public function destroy(Student $student)
    {
        $student->loadCount('enrollments');

        if ($student->enrollments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "⚠️ No se puede eliminar este estudiante: tiene {$student->enrollments_count} inscripciones registradas.",
            ], Response::HTTP_CONFLICT);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Estudiante eliminado exitosamente.'
        ], Response::HTTP_OK);
    }

    /* ======================================================
     *  🧩 FUNCIONES PARA EL PANEL DEL ESTUDIANTE
     * ====================================================== */

    /** 🏠 Dashboard del estudiante */
    public function dashboardStudent()
    {
        $student = Student::where('user_id', Auth::id())->with('branch')->firstOrFail();

        $enrollments = Enrollment::with(['offering.course', 'grade'])
            ->where('student_id', $student->id)
            ->get();

        $promedio = round($enrollments->avg('grade.total'), 2);
        $aprobados = $enrollments->where('grade.estado', 'Aprobado')->count();
        $recuperacion = $enrollments->where('grade.estado', 'Recuperación')->count();
        $reprobados = $enrollments->where('grade.estado', 'Reprobado')->count();

        $notasRecientes = Grade::with(['enrollment.offering.course'])
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->orderByDesc('updated_at')
            ->take(3)
            ->get()
            ->map(fn($g) => [
                'curso' => $g->enrollment->offering->course->nombre,
                'parcial1' => $g->parcial1,
                'parcial2' => $g->parcial2,
                'final' => $g->final,
                'total' => $g->total,
                'estado' => $g->estado,
                'fecha' => $g->updated_at?->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'nombre' => $student->nombres,
                'sucursal' => $student->branch->nombre,
                'grado' => $student->grade,
                'nivel' => $student->level,
                'promedio' => $promedio,
                'aprobados' => $aprobados,
                'recuperacion' => $recuperacion,
                'reprobados' => $reprobados,
                'notas_recientes' => $notasRecientes,
            ],
        ]);
    }

    /** 📚 Cursos inscritos del estudiante */
    public function myCourses()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $courses = Enrollment::with([
            'offering.course',
            'offering.teacher.user',
            'offering.branch',
            'grade'
        ])
            ->where('student_id', $student->id)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'curso' => $e->offering->course->nombre,
                'catedratico' => $e->offering->teacher->user->name,
                'nivel' => $e->offering->level,
                'grado' => $e->offering->grade,
                'sucursal' => $e->offering->branch->nombre,
                'parcial1' => $e->grade->parcial1 ?? 0,
                'parcial2' => $e->grade->parcial2 ?? 0,
                'final' => $e->grade->final ?? 0,
                'total' => $e->grade->total ?? 0,
                'estado' => $e->grade->estado ?? 'Sin calificar',
            ]);

        return response()->json(['success' => true, 'data' => $courses]);
    }

    /** 📊 Desempeño general del estudiante */
    public function performance()
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();

        $enrollments = Enrollment::with(['offering.course', 'grade'])
            ->where('student_id', $student->id)
            ->get();

        $promedio = round($enrollments->avg('grade.total'), 2);
        $aprobados = $enrollments->where('grade.estado', 'Aprobado')->count();
        $recuperacion = $enrollments->where('grade.estado', 'Recuperación')->count();
        $reprobados = $enrollments->where('grade.estado', 'Reprobado')->count();

        $detalle = $enrollments->map(fn($e) => [
            'curso' => $e->offering->course->nombre,
            'catedratico' => $e->offering->teacher->user->name,
            'total' => $e->grade->total ?? 0,
            'estado' => $e->grade->estado ?? 'Sin calificar',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'promedio' => $promedio,
                'aprobados' => $aprobados,
                'recuperacion' => $recuperacion,
                'reprobados' => $reprobados,
                'detalle' => $detalle,
            ],
        ]);
    }
}
