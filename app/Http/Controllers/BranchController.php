<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /** Listar sucursales con métricas */
    public function index()
    {
        return Branch::withMetrics()
            ->orderBy('nombre')
            ->get();
    }

    /** Crear sucursal */
    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre'    => 'required|string|max:100|unique:branches,nombre',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);

        return response()->json(Branch::create($data), 201);
    }

    /** Ver una sucursal */
    public function show(Branch $branch)
    {
        return $branch->load(['students', 'teachers', 'offerings']);
    }

    /** Actualizar sucursal */
    public function update(Request $r, Branch $branch)
    {
        $data = $r->validate([
            'nombre'    => 'sometimes|required|string|max:100|unique:branches,nombre,' . $branch->id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $branch->update($data);
        return $branch;
    }

    /** Eliminar sucursal */
    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return response()->json(['message' => 'Sucursal eliminada']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se puede eliminar la sucursal (tiene dependencias)',
                'error'   => $e->getMessage()
            ], 400);
        }
    }
}
