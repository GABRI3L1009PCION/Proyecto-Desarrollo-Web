<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Branch;
use App\Models\Offering;

class SecretariaCatedraticoController extends Controller
{
    /**
     * Mostrar la lista de catedráticos con filtros.
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'branch', 'offerings.course']);

        // === FILTROS ===
        if ($request->filled('sucursal')) {
            $query->whereHas('branch', function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->sucursal}%");
            });
        }

        if ($request->filled('nombre')) {
            $query->where('nombres', 'like', "%{$request->nombre}%");
        }

        $teachers = $query->orderByDesc('id')->get();

        return view('Secretaria.catedraticos', compact('teachers'));
    }

    /**
     * Guardar un nuevo catedrático.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'nombres' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        // Verificar que el usuario no tenga ya catedrático asignado
        if (Teacher::where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'Este usuario ya tiene un perfil de catedrático asignado.');
        }

        Teacher::create([
            'user_id' => $request->user_id,
            'branch_id' => $request->branch_id,
            'nombres' => $request->nombres,
            'telefono' => $request->telefono,
        ]);

        return back()->with('success', 'Catedrático registrado correctamente.');
    }

    /**
     * Actualizar información básica de un catedrático.
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'nombres' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $teacher->update([
            'nombres' => $request->nombres,
            'telefono' => $request->telefono,
            'branch_id' => $request->branch_id,
        ]);

        return back()->with('success', 'Datos del catedrático actualizados correctamente.');
    }

    /**
     * Eliminar un catedrático (solo si no tiene cursos asignados).
     */
    public function destroy($id)
    {
        $teacher = Teacher::with('offerings')->findOrFail($id);

        if ($teacher->offerings->count() > 0) {
            return back()->with('error', 'No se puede eliminar un catedrático con cursos asignados.');
        }

        $teacher->delete();
        return back()->with('success', 'Catedrático eliminado correctamente.');
    }

    /**
     * Ver cursos asignados (solo lectura).
     */
    public function cursos($id)
    {
        $teacher = Teacher::with(['offerings.course', 'offerings.branch'])->findOrFail($id);
        return response()->json([
            'nombre' => $teacher->nombres,
            'cursos' => $teacher->offerings,
        ]);
    }
}
