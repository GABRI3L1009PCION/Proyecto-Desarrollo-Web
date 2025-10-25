<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Grade;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeacherController extends Controller
{
    /** ======================================================
     * 📋 Listar catedráticos con sus relaciones
     * ====================================================== */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'branch', 'offerings.course']);

        // 🔍 Filtros opcionales
        if ($search = $request->input('q')) {
            $query->where('nombres', 'like', "%{$search}%")
                ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($branch = $request->input('branch_id')) {
            $query->where('branch_id', $branch);
        }

        $teachers = $query->orderBy('nombres')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => '📋 Lista de catedráticos obtenida correctamente.',
            'count'   => $teachers->total(),
            'data'    => $teachers->items(),
            'pagination' => [
                'current_page' => $teachers->currentPage(),
                'last_page'    => $teachers->lastPage(),
                'per_page'     => $teachers->perPage(),
            ]
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 🔍 Usuarios con rol "catedrático" sin vincular a Teacher
     * ====================================================== */
    public function unlinkedUsers()
    {
        $users = User::where('role', 'catedratico')
            ->whereDoesntHave('teacher')
            ->select('id', 'name', 'email')
            ->orderBy('email')
            ->get();

        return response()->json($users, Response::HTTP_OK);
    }

    /** ======================================================
     * ➕ Registrar un nuevo catedrático
     * ====================================================== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|exists:users,id|unique:teachers,user_id',
            'branch_id' => 'required|exists:branches,id',
            'nombres'   => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $teacher = Teacher::create($data);

        return response()->json([
            'success' => true,
            'message' => '✅ Catedrático registrado correctamente.',
            'data'    => $teacher->load(['user', 'branch'])
        ], Response::HTTP_CREATED);
    }

    /** ======================================================
     * 👀 Ver detalles de un catedrático
     * ====================================================== */
    public function show(Teacher $teacher)
    {
        return response()->json([
            'success' => true,
            'message' => '👀 Detalles del catedrático obtenidos.',
            'data'    => $teacher->load(['user', 'branch', 'offerings.course', 'offerings.branch'])
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * ✏️ Actualizar datos de un catedrático
     * ====================================================== */
    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'branch_id' => 'sometimes|exists:branches,id',
            'nombres'   => 'sometimes|required|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $teacher->update($data);

        return response()->json([
            'success' => true,
            'message' => '✏️ Datos del catedrático actualizados correctamente.',
            'data'    => $teacher->load(['user', 'branch'])
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * ❌ Eliminar catedrático (con verificación de dependencias)
     * ====================================================== */
    public function destroy(Teacher $teacher)
    {
        $teacher->load(['offerings.enrollments']);

        $totalCursos = $teacher->offerings->count();
        $totalAlumnos = $teacher->offerings->sum(fn($o) => $o->enrollments->count());

        if ($totalCursos > 0) {
            if ($totalAlumnos > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ No se puede eliminar este catedrático: tiene $totalCursos cursos con $totalAlumnos alumnos inscritos.",
                ], Response::HTTP_CONFLICT);
            }

            return response()->json([
                'success' => false,
                'message' => "⚠️ No se puede eliminar este catedrático: tiene cursos asignados sin alumnos.",
            ], Response::HTTP_CONFLICT);
        }

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Catedrático eliminado exitosamente.'
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 📊 DASHBOARD DEL CATEDRÁTICO (Resumen)
     * ====================================================== */
    public function dashboard(Request $request)
    {
        $teacherId = $request->user()->teacher_id ?? null;

        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no tiene un catedrático asociado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $teacher = Teacher::with([
            'offerings.course:id,nombre',
            'offerings.branch:id,nombre',
            'offerings.enrollments:id,offering_id',
        ])->find($teacherId);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Catedrático no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $totalCursos = $teacher->offerings->count();
        $totalAlumnos = $teacher->offerings->sum(fn($o) => $o->enrollments->count());

        $promedioGeneral = Grade::whereHas('enrollment.offering', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->avg('total');

        $promedioGeneral = $promedioGeneral ? round($promedioGeneral, 2) : 0;

        $cursos = $teacher->offerings->map(fn($o) => [
            'id' => $o->id,
            'curso' => $o->course->nombre ?? '—',
            'grado' => $o->grade,
            'nivel' => $o->level,
            'sucursal' => $o->branch->nombre ?? '—',
            'alumnos_inscritos' => $o->enrollments->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Panel del catedrático cargado correctamente.',
            'data' => [
                'estadisticas' => [
                    'totalCursos' => $totalCursos,
                    'totalAlumnos' => $totalAlumnos,
                    'promedioGeneral' => $promedioGeneral,
                ],
                'cursos' => $cursos,
            ],
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 🎓 LISTA DE CURSOS FILTRADOS (para "Mis Cursos")
     * ====================================================== */
    public function courses(Request $request)
    {
        $teacherId = $request->user()->teacher_id ?? null;

        if (!$teacherId) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario autenticado no tiene un catedrático asociado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $query = \App\Models\Offering::with(['course', 'branch', 'enrollments'])
            ->where('teacher_id', $teacherId);

        // 🔍 Búsqueda global
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('course', fn($qc) => $qc->where('nombre', 'like', "%{$search}%"))
                    ->orWhere('grade', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%")
                    ->orWhere('ciclo', 'like', "%{$search}%");
            });
        }

        // 🎯 Filtros
        if ($grado = $request->input('grado')) $query->where('grade', $grado);
        if ($nivel = $request->input('nivel')) $query->where('level', $nivel);
        if ($ciclo = $request->input('ciclo')) $query->where('ciclo', $ciclo);
        if ($cupo = $request->input('cupo')) $query->where('cupo', $cupo);

        // ⚙️ Ordenamiento
        switch ($request->input('orden', 'recientes')) {
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'az':
                $query->orderBy(
                    \App\Models\Course::select('nombre')
                        ->whereColumn('courses.id', 'offerings.course_id')
                );
                break;
            case 'za':
                $query->orderByDesc(
                    \App\Models\Course::select('nombre')
                        ->whereColumn('courses.id', 'offerings.course_id')
                );
                break;
            case 'mas_alumnos':
                $query->withCount('enrollments')->orderBy('enrollments_count', 'desc');
                break;
            case 'menos_alumnos':
                $query->withCount('enrollments')->orderBy('enrollments_count', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $courses = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lista de cursos asignados cargada correctamente.',
            'count'   => $courses->total(),
            'data'    => $courses->map(fn($o) => [
                'id' => $o->id,
                'curso' => $o->course->nombre ?? '—',
                'grado' => $o->grade,
                'nivel' => $o->level,
                'sucursal' => $o->branch->nombre ?? '—',
                'anio' => $o->anio,
                'ciclo' => $o->ciclo,
                'cupo' => $o->cupo,
                'alumnos' => $o->enrollments->count(),
            ]),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
            ],
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 🧾 LISTAR CALIFICACIONES DE UN CURSO
     * ====================================================== */
    public function courseGrades(Request $request, $offeringId)
    {
        $teacherId = $request->user()->teacher_id ?? null;

        $offering = Offering::with([
            'course',
            'enrollments.student.user',
            'enrollments.grade'
        ])
            ->where('id', $offeringId)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$offering) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado o no pertenece al catedrático autenticado.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Datos de notas
        $grades = $offering->enrollments->map(fn($e) => [
            'id' => $e->id,
            'alumno' => $e->student->nombres ?? 'Desconocido',
            'parcial1' => $e->grade->parcial1 ?? null,
            'parcial2' => $e->grade->parcial2 ?? null,
            'final' => $e->grade->final ?? null,
            'total' => $e->grade->total ?? 0,
            'estado' => $e->grade->estado ?? '—',
        ]);

        // Promedios y conteos
        $promedio = round($grades->avg('total') ?? 0, 2);
        $aprobados = $grades->where('estado', 'Aprobado')->count();
        $recuperacion = $grades->where('estado', 'Recuperación')->count();
        $reprobados = $grades->where('estado', 'Reprobado')->count();

        return response()->json([
            'success' => true,
            'message' => 'Calificaciones del curso cargadas correctamente.',
            'curso' => [
                'nombre' => $offering->course->nombre,
                'grado' => $offering->grade,
                'nivel' => $offering->level,
            ],
            'resumen' => compact('promedio', 'aprobados', 'recuperacion', 'reprobados'),
            'data' => $grades,
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * ✏️ REGISTRAR O ACTUALIZAR CALIFICACIÓN DE UN ALUMNO
     * ====================================================== */
    public function gradeStudent(Request $request, $enrollmentId)
    {
        $teacherId = $request->user()->teacher_id ?? null;

        $enrollment = \App\Models\Enrollment::where('id', $enrollmentId)
            ->whereHas('offering', fn($q) => $q->where('teacher_id', $teacherId))
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'El alumno no pertenece a un curso del catedrático autenticado.',
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'parcial1' => 'nullable|numeric|min:0|max:30',
            'parcial2' => 'nullable|numeric|min:0|max:30',
            'final' => 'nullable|numeric|min:0|max:40',
        ]);

        // Reglas de secuencia (como en tu modal)
        if (!is_null($data['parcial2'] ?? null) && is_null($data['parcial1'] ?? null)) {
            return response()->json(['message' => '⚠ No puedes asignar Parcial 2 sin Parcial 1.'], 422);
        }
        if (!is_null($data['final'] ?? null) && (is_null($data['parcial1'] ?? null) || is_null($data['parcial2'] ?? null))) {
            return response()->json(['message' => '⚠ No puedes asignar Final sin los parciales previos.'], 422);
        }

        // Cálculo de total y estado
        $total = ($data['parcial1'] ?? 0) + ($data['parcial2'] ?? 0) + ($data['final'] ?? 0);
        $estado = $total >= 70 ? 'Aprobado' : ($total >= 60 ? 'Recuperación' : 'Reprobado');

        $grade = \App\Models\Grade::updateOrCreate(
            ['enrollment_id' => $enrollmentId],
            array_merge($data, compact('total', 'estado'))
        );

        return response()->json([
            'success' => true,
            'message' => '✅ Calificación guardada correctamente.',
            'grade' => $grade->load('enrollment.student.user', 'enrollment.offering.course'),
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * 👩‍🎓 LISTA DE ALUMNOS DE UN CURSO (Ver alumnos)
     * ====================================================== */
    public function courseStudents(Request $request, $offeringId)
    {
        $teacherId = $request->user()->teacher_id ?? null;

        $offering = Offering::with(['course', 'branch', 'enrollments.student.user', 'enrollments.grade'])
            ->where('id', $offeringId)
            ->where('teacher_id', $teacherId)
            ->first();

        if (!$offering) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado o no pertenece al catedrático autenticado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $students = $offering->enrollments->map(fn($e) => [
            'id' => $e->student->id,
            'nombre' => $e->student->nombres,
            'email' => $e->student->user->email ?? null,
            'grado' => $e->student->grade,
            'nivel' => $e->student->level,
            'estado' => $e->status,
            'parcial1' => $e->grade->parcial1 ?? null,
            'parcial2' => $e->grade->parcial2 ?? null,
            'final' => $e->grade->final ?? null,
            'total' => $e->grade->total ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lista de alumnos cargada correctamente.',
            'curso' => [
                'nombre' => $offering->course->nombre,
                'grado' => $offering->grade,
                'nivel' => $offering->level,
                'sucursal' => $offering->branch->nombre,
            ],
            'data' => $students,
        ], Response::HTTP_OK);
    }
}
