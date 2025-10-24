<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User; // Asegúrate de importar el modelo User
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeacherController extends Controller
{
    /** ======================================================
     * 📋 Listar catedráticos con sus relaciones
     * ====================================================== */
    public function index()
    {
        // NOTA: Para que los filtros del frontend funcionen eficientemente (búsqueda por nombre/sucursal),
        // este método debe ser actualizado para aceptar los parámetros $request->input('q') y $request->input('branch_id'),
        // de lo contrario, el filtrado se hará en el frontend (lo cual es menos eficiente).

        $teachers = Teacher::with(['user', 'branch', 'offerings.course'])
            ->orderBy('nombres')
            ->paginate(15);

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
     * * Esta función es necesaria para poblar el dropdown/selector de "Usuario Asociado"
     * en el formulario de creación de catedráticos.
     * ====================================================== */
    public function unlinkedUsers()
    {
        // Asegura que solo trae usuarios con el rol 'catedratico' que NO tienen un registro en la tabla 'teachers'
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
}
