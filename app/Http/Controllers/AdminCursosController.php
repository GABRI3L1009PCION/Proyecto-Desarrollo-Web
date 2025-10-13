<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class AdminCursosController extends Controller
{
    /**
     * Mostrar la lista de cursos.
     */
    public function index()
    {
        $courses = Course::all();
        return view('Administrador.cursos_admin', compact('courses')); // ✅ ruta correcta de tu blade
    }

    /**
     * Guardar un nuevo curso.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo'      => 'required|string|max:20|unique:courses,codigo',
            'nombre'      => 'required|string|max:255',
            'creditos'    => 'nullable|integer|min:0|max:20',
            'descripcion' => 'nullable|string',
        ]);

        Course::create($request->only('codigo', 'nombre', 'creditos', 'descripcion'));

        return redirect()->route('administrador.cursos')
            ->with('success', '✅ Curso registrado correctamente.');
    }

    /**
     * Actualizar un curso existente.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'codigo'      => 'required|string|max:20|unique:courses,codigo,' . $course->id,
            'nombre'      => 'required|string|max:255',
            'creditos'    => 'nullable|integer|min:0|max:20',
            'descripcion' => 'nullable|string',
        ]);

        $course->update($request->only('codigo', 'nombre', 'creditos', 'descripcion'));

        return redirect()->route('administrador.cursos')
            ->with('success', '✏️ Curso actualizado correctamente.');
    }

    /**
     * Eliminar un curso.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('administrador.cursos')
            ->with('success', '🗑️ Curso eliminado correctamente.');
    }
}
