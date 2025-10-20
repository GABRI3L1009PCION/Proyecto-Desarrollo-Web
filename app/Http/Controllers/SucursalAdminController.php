<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class SucursalAdminController extends Controller
{
    /**
     * 📋 Mostrar todas las sucursales con métricas asociadas
     * (alumnos, catedráticos y cursos)
     */
    public function index()
    {
        // Usa el scope que ya tienes (withMetrics) y añade más conteos
        $branches = Branch::withMetrics()
            ->withCount(['teachers', 'courses'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Administrador.sucursales_admin', compact('branches'));
    }

    /**
     * ➕ Crear una nueva sucursal desde el panel web
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:100|unique:branches,nombre',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);

        Branch::create($validated);

        return redirect()->route('administrador.sucursales')
            ->with('success', 'Sucursal creada exitosamente.');
    }

    /**
     * ✏️ Actualizar datos de una sucursal
     */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'nombre'    => 'required|string|max:100|unique:branches,nombre,' . $branch->id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $branch->update($validated);

        return redirect()->route('administrador.sucursales')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    /**
     * ❌ Eliminar una sucursal (solo si no tiene dependencias)
     */
    public function destroy($id)
    {
        $branch = Branch::withCount(['students', 'teachers', 'offerings'])->findOrFail($id);

        // Validar dependencias antes de eliminar
        if (
            $branch->students_count > 0 ||
            $branch->teachers_count > 0 ||
            $branch->offerings_count > 0
        ) {
            return redirect()->route('administrador.sucursales')
                ->with('error', 'No se puede eliminar la sucursal porque tiene registros asociados.');
        }

        $branch->delete();

        return redirect()->route('administrador.sucursales')
            ->with('success', 'Sucursal eliminada exitosamente.');
    }
}
