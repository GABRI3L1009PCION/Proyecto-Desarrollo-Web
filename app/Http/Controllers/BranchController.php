<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Response; // Para usar constantes HTTP

class BranchController extends Controller
{
    /** * 📋 API: Listar sucursales con métricas, búsqueda y ordenación
     * La aplicación móvil enviará 'q' y 'sort'
     */
    public function index(Request $request)
    {
        // 1. Inicializar la consulta y solicitar los conteos de estudiantes y CATEDRÁTICOS
        // Se añade withCount(['teachers']) a la consulta inicial
        $query = Branch::withMetrics()->withCount(['teachers']);

        // 2. Aplicar filtro de búsqueda (q)
        if ($q = $request->input('q')) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('nombre', 'like', "%{$q}%")
                    ->orWhere('direccion', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            });
        }

        // 3. Aplicar ordenación (sort)
        $sort = $request->input('sort', 'recientes');
        switch ($sort) {
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('nombre', 'asc');
                break;
            case 'inverso':
                $query->orderBy('nombre', 'desc');
                break;
            case 'recientes':
            default:
                // Si no se especifica orden, o es "recientes", usa el más nuevo primero
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 4. Obtener resultados
        $branches = $query->get();

        return response()->json($branches, Response::HTTP_OK);
    }

    /** ➕ API: Crear sucursal */
    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre'    => 'required|string|max:100|unique:branches,nombre',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'required|string|max:30',
        ]);

        $branch = Branch::create($data);

        return response()->json([
            'message' => '✅ Sucursal creada exitosamente.',
            'data'    => $branch
        ], Response::HTTP_CREATED);
    }

    /** 👀 API: Ver una sucursal */
    public function show(Branch $branch)
    {
        return response()->json($branch->load(['students', 'teachers', 'offerings']), Response::HTTP_OK);
    }

    /** ✏️ API: Actualizar sucursal */
    public function update(Request $r, Branch $branch)
    {
        $data = $r->validate([
            'nombre'    => 'sometimes|required|string|max:100|unique:branches,nombre,' . $branch->id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'sometimes|required|string|max:30',
        ]);

        $branch->update($data);

        return response()->json([
            'message' => '✏️ Sucursal actualizada correctamente.',
            'data'    => $branch
        ], Response::HTTP_OK);
    }

    /** ❌ API: Eliminar sucursal */
    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return response()->json(['message' => '🗑️ Sucursal eliminada exitosamente.'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => '❌ No se puede eliminar la sucursal (Verifique que no tenga dependencias).',
                'error'   => $e->getMessage()
            ], Response::HTTP_CONFLICT); // 409 Conflict
        }
    }
}
