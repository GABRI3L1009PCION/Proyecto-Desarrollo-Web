<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnrollmentController extends Controller
{
    // ======================================================
    // 📋 GET /enrollments — Listado con filtros, orden y límite
    // ======================================================
    public function index(Request $request)
    {
        $query = Enrollment::with([
            'student.user',
            'offering.course',
            'offering.teacher.user',
            'offering.branch'
        ]);

        // === 🧩 FILTROS ===

        // 1. FILTRO POR ESTADO
        $query->when($request->estado, fn($q) =>
        $q->where('status', $request->estado)
        );

        // 2. FILTRO POR CURSO ID
        $query->when($request->curso_id, fn($q) =>
        $q->whereHas('offering.course', fn($qc) =>
        $qc->where('id', $request->curso_id)
        )
        );

        // 🔑 3. FILTRO POR SUCURSAL ID (branch_id)
        $query->when($request->branch_id, fn($q) =>
        $q->whereHas('offering.branch', fn($qb) =>
        $qb->where('id', $request->branch_id)
        )
        );

        // 🔑 4. BÚSQUEDA GENERAL (q) por Nombre de Alumno o Nombre de Curso
        if ($q = $request->input('q')) {
            $query->where(function ($sq) use ($q) {

                // === INICIO DE LA CORRECCIÓN ===

                // Opción 1 (Recomendada): Busca el nombre en la tabla 'students', columna 'nombres'
                $sq->whereHas('student', fn($qs) =>
                $qs->where('nombres', 'like', "%{$q}%")
                )
                    // Opción 2 (Adicional): También puedes buscar en el 'user.name' como fallback si lo necesitas
                    // ->orWhereHas('student.user', fn($qu) =>
                    //     $qu->where('name', 'like', "%{$q}%")
                    // )

                    // OR Busca en el nombre del curso
                    ->orWhereHas('offering.course', fn($qc) =>
                    $qc->where('nombre', 'like', "%{$q}%")
                    );

                // === FIN DE LA CORRECCIÓN ===
            });
        }

        // === 🔃 ORDENAMIENTO ===
        switch ($request->input('sort', 'latest')) {
            case 'oldest':
                $query->orderBy('fecha', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('fecha', 'desc');
                break;
        }

        // === 📏 LÍMITE (Dashboard rápido) ===
        if ($limit = $request->input('limit')) {
            $enrollments = $query->take($limit)->get();
            // CORRECCIÓN: Si usas límite, el total de inscripciones es el total GLOBAL (Enrollment::count()).
            $totalCount = Enrollment::count();

            return response()->json([
                'success' => true,
                'filters' => [
                    'estado'   => $request->estado,
                    'curso_id' => $request->curso_id,
                    'branch_id' => $request->branch_id,
                    'q'        => $request->q,
                    'sort'     => $request->sort,
                    'limit'    => $limit,
                ],
                'pagination' => [
                    'total' => $totalCount,
                    'count' => $enrollments->count(),
                ],
                'data' => $enrollments,
            ], Response::HTTP_OK);
        }

        // === 📄 PAGINACIÓN COMPLETA ===
        $paginated = $query->paginate(10);

        return response()->json([
            'success' => true,
            'filters' => [
                'estado'   => $request->estado,
                'curso_id' => $request->curso_id,
                'branch_id' => $request->branch_id,
                'q'        => $request->q,
                'sort'     => $request->sort,
            ],
            'pagination' => [
                'total'         => $paginated->total(),
                'current_page'  => $paginated->currentPage(),
                'last_page'     => $paginated->lastPage(),
                'per_page'      => $paginated->perPage(),
            ],
            'data' => $paginated->items(),
        ], Response::HTTP_OK);
    }

    // ... (Resto de los métodos store, show, update, destroy sin cambios)

    // ======================================================
    // ➕ POST /enrollments — Crear inscripción
    // ======================================================
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'offering_id' => 'required|exists:offerings,id',
            'status'      => 'nullable|in:activa,retirada,finalizada',
        ]);

        // ⚠️ Evitar duplicados
        if (Enrollment::where('student_id', $data['student_id'])
            ->where('offering_id', $data['offering_id'])
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante ya está inscrito en esta oferta'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ✅ Crear inscripción
        $enrollment = Enrollment::create([
            'student_id'  => $data['student_id'],
            'offering_id' => $data['offering_id'],
            'status'      => $data['status'] ?? 'activa',
            'fecha'       => now(),
        ]);

        // 🔻 Reducir cupo si aplica
        $offering = Offering::find($data['offering_id']);
        if ($offering && $offering->cupo > 0) {
            $offering->decrement('cupo');
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Inscripción registrada correctamente',
            'data'       => $enrollment->load('student.user', 'offering.course', 'offering.branch'),
        ], Response::HTTP_CREATED);
    }

    // ======================================================
    // 🔍 GET /enrollments/{id} — Mostrar inscripción
    // ======================================================
    public function show(Enrollment $enrollment)
    {
        return response()->json([
            'success' => true,
            'data' => $enrollment->load([
                'student.user',
                'offering.course',
                'offering.teacher.user',
                'offering.branch'
            ])
        ], Response::HTTP_OK);
    }

    // ======================================================
    // ✏️ PUT/PATCH /enrollments/{id} — Actualizar estado
    // ======================================================
    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'status' => 'required|in:activa,retirada,finalizada',
        ]);

        $oldStatus = $enrollment->status;
        $enrollment->update($data);

        // 🔄 Ajuste de cupos según transición
        $offering = Offering::find($enrollment->offering_id);
        if ($offering) {
            if (in_array($data['status'], ['retirada', 'finalizada']) && $oldStatus === 'activa') {
                $offering->increment('cupo');
            } elseif ($data['status'] === 'activa' && in_array($oldStatus, ['retirada', 'finalizada']) && $offering->cupo > 0) {
                $offering->decrement('cupo');
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Estado de la inscripción actualizado correctamente',
            'data'       => $enrollment->load('student.user', 'offering.course', 'offering.branch'),
        ], Response::HTTP_OK);
    }

    // ======================================================
    // 🗑️ DELETE /enrollments/{id} — Eliminar inscripción
    // ======================================================
    public function destroy(Enrollment $enrollment)
    {
        $offering = Offering::find($enrollment->offering_id);
        if ($offering) {
            $offering->increment('cupo');
        }

        $enrollment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción eliminada correctamente',
        ], Response::HTTP_OK);
    }
}
