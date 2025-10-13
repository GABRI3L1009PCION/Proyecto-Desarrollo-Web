<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Branch;
use App\Models\Offering;
use App\Models\Course;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    /**
     * 📋 Mostrar todos los catedráticos con su usuario y sucursal.
     */
    public function index()
    {
        $teachers = Teacher::with(['user', 'branch', 'offerings.course', 'offerings.branch'])->paginate(10);
        return view('Administrador.catedraticos_admin', compact('teachers'));
    }

    /**
     * ➕ Crear nuevo catedrático (asignar usuario existente).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id|unique:teachers,user_id',
            'branch_id' => 'required|exists:branches,id',
            'nombres'   => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        Teacher::create($validated);

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Catedrático registrado correctamente.');
    }

    /**
     * ✏️ Actualizar información del catedrático.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'nombres'   => 'required|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $teacher = Teacher::findOrFail($id);
        $teacher->update($validated);

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Datos del catedrático actualizados correctamente.');
    }

    /**
     * ❌ Eliminar un catedrático (con verificación de asignaciones y alumnos inscritos).
     */
    public function destroy($id)
    {
        $teacher = Teacher::with(['offerings.enrollments'])->findOrFail($id);

        // 1️⃣ Verificar si tiene cursos asignados
        if ($teacher->offerings->count() > 0) {

            // 2️⃣ Contar total de alumnos inscritos en sus cursos
            $totalAlumnos = 0;
            foreach ($teacher->offerings as $offering) {
                $totalAlumnos += $offering->enrollments->count();
            }

            // 3️⃣ Bloquear eliminación con mensaje claro
            if ($totalAlumnos > 0) {
                return redirect()->route('administrador.catedraticos')
                    ->with('error', "No se puede eliminar este catedrático porque tiene cursos con alumnos inscritos ({$totalAlumnos} en total).");
            } else {
                return redirect()->route('administrador.catedraticos')
                    ->with('error', 'No se puede eliminar este catedrático porque tiene cursos asignados. Elimine o reasigne sus cursos antes de proceder.');
            }
        }

        // 4️⃣ Si no tiene asignaciones ni alumnos, eliminar normalmente
        $teacher->delete();

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Catedrático eliminado correctamente.');
    }

    /**
     * 📘 Obtener los cursos asignados (solo lectura para el modal).
     */
    public function getCursos($id)
    {
        $teacher = Teacher::with(['offerings.course', 'offerings.branch'])->find($id);

        if (!$teacher) {
            return response()->json(['error' => 'Catedrático no encontrado'], 404);
        }

        return response()->json($teacher->offerings);
    }

    /**
     * 📗 Asignar un curso a un catedrático.
     */
    public function asignarCurso(Request $request, $id)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
            'grade'     => 'required|string',
            'level'     => 'required|string',
            'anio'      => 'nullable|integer',
            'ciclo'     => 'nullable|string|max:10',
            'cupo'      => 'nullable|integer|min:1',
            'horario'   => 'nullable|string|max:255',
        ]);

        Offering::create([
            'course_id'  => $request->course_id,
            'teacher_id' => $id,
            'branch_id'  => $request->branch_id,
            'grade'      => $request->grade,
            'level'      => $request->level,
            'anio'       => $request->anio ?? date('Y'),
            'ciclo'      => $request->ciclo ?? 'A',
            'cupo'       => $request->cupo ?? 30,
            'horario'    => $request->horario,
        ]);

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Curso asignado correctamente al catedrático.');
    }

    /**
     * 🗑️ Eliminar una asignación de curso (versión web).
     */
    public function eliminarAsignacion($id)
    {
        $offering = Offering::findOrFail($id);
        $offering->delete();

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Asignación eliminada correctamente.');
    }

    /**
     * 🔄 Actualizar datos de una asignación (versión web).
     */
    public function actualizarAsignacion(Request $request, $id)
    {
        $offering = Offering::findOrFail($id);

        $offering->update($request->validate([
            'grade'   => 'required|string',
            'level'   => 'required|string',
            'anio'    => 'nullable|integer',
            'ciclo'   => 'nullable|string|max:10',
            'cupo'    => 'nullable|integer|min:1',
            'horario' => 'nullable|string|max:255',
        ]));

        return redirect()->route('administrador.catedraticos')
            ->with('success', 'Asignación de curso actualizada correctamente.');
    }
}
