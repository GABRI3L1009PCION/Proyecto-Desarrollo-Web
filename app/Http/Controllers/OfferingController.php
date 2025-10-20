<?php
namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;

class OfferingController extends Controller
{
    // GET /offerings
    public function index()
    {
        // Trae las ofertas con curso, docente y sucursal
        return response()->json(
            Offering::with(['course', 'teacher', 'branch'])->paginate(10)
        );
    }

    // POST /offerings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'  => 'required|exists:courses,id',
            'branch_id'  => 'required|exists:branches,id',
            'teacher_id' => 'required|exists:teachers,id',
            'anio'       => 'required|integer|min:2000',
            'ciclo'      => 'required|string|max:10',
            'horario'    => 'nullable|string|max:255',
            'cupo'       => 'required|integer|min:1',
            'grade'      => 'required|in:Novatos,Expertos',
            'level'      => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $offering = Offering::create($validated);

        return response()->json($offering, 201);
    }

    // GET /offerings/{id}
    public function show($id)
    {
        $offering = Offering::with(['course', 'teacher', 'branch', 'enrollments'])->findOrFail($id);
        return response()->json($offering);
    }

    // PUT/PATCH /offerings/{id}
    public function update(Request $request, $id)
    {
        $offering = Offering::findOrFail($id);

        $validated = $request->validate([
            'teacher_id' => 'sometimes|exists:teachers,id',
            'branch_id'  => 'sometimes|exists:branches,id',
            'horario'    => 'nullable|string|max:255',
            'cupo'       => 'sometimes|integer|min:1',
            'anio'       => 'sometimes|integer|min:2000',
            'ciclo'      => 'sometimes|string|max:10',
            'grade'      => 'sometimes|in:Novatos,Expertos',
            'level'      => 'sometimes|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
        ]);

        $offering->update($validated);

        return response()->json($offering);
    }

    // DELETE /offerings/{id}
    public function destroy($id)
    {
        $offering = Offering::findOrFail($id);
        $offering->delete();

        return response()->json(['message' => 'Oferta eliminada']);
    }
}
