<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    /** ======================================================
     * 📋 Listar cursos con paginación, búsqueda y orden
     * ====================================================== */
    // GET /courses
    public function index(Request $request)
    {
        $query = Course::query();

        // 🔍 Búsqueda general (código o nombre)
        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%");
            });
        }

        // 🔄 Ordenamiento
        switch ($request->input('sort', 'recientes')) {
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('nombre', 'asc');
                break;
            case 'recientes':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 📄 Paginación (10 por página, como estaba definido)
        $courses = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => '📋 Lista de cursos obtenida correctamente.',
            'count'   => $courses->total(),
            'data'    => $courses->items(),
            'last_page' => $courses->lastPage(), // Incluimos esto para el frontend
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page'    => $courses->lastPage(),
                'per_page'     => $courses->perPage(),
            ]
        ], Response::HTTP_OK);
    }

    // POST /courses
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'      => 'required|string|max:20|unique:courses,codigo',
            'nombre'      => 'required|string|max:255',
            'creditos'    => 'nullable|integer|min:0|max:20',
            'descripcion' => 'nullable|string',
        ]);

        $course = Course::create($data);

        return response()->json([
            'success' => true,
            'message' => '✅ Curso registrado correctamente.',
            'data'    => $course
        ], Response::HTTP_CREATED);
    }

    // GET /courses/{id}
    public function show(Course $course)
    {
        return response()->json([
            'success' => true,
            'message' => '👀 Detalles del curso obtenidos.',
            'data'    => $course->load('offerings')
        ]);
    }

    // PUT/PATCH /courses/{id}
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'codigo'      => 'sometimes|required|string|max:20|unique:courses,codigo,' . $course->id,
            'nombre'      => 'sometimes|required|string|max:255',
            'creditos'    => 'nullable|integer|min:0|max:20',
            'descripcion' => 'nullable|string',
        ]);

        $course->update($data);

        return response()->json([
            'success' => true,
            'message' => '✏️ Curso actualizado correctamente.',
            'data'    => $course
        ]);
    }

    // DELETE /courses/{id}
    public function destroy(Course $course)
    {
        // Se asume que el backend verifica si hay ofertas o inscripciones dependientes
        // antes de eliminar el curso.
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Curso eliminado exitosamente.'
        ], Response::HTTP_OK);
    }
}
