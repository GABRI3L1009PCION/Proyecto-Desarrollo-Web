<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GradeController extends Controller
{
    // GET /grades
    public function index()
    {
        return response()->json(
            Grade::with(['enrollment.student.user', 'enrollment.offering.course'])
                ->paginate(10)
        );
    }

    // POST /grades
    public function store(Request $request)
    {
        $data = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id|unique:grades,enrollment_id',
            'parcial1'      => 'nullable|numeric|min:0|max:100',
            'parcial2'      => 'nullable|numeric|min:0|max:100',
            'final'         => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $grade = Grade::create($data);

        return response()->json($grade->load('enrollment.student.user'), Response::HTTP_CREATED);
    }

    // GET /grades/{id}
    public function show(Grade $grade)
    {
        return response()->json(
            $grade->load(['enrollment.student.user', 'enrollment.offering.course'])
        );
    }

    // PUT/PATCH /grades/{id}
    public function update(Request $request, Grade $grade)
    {
        $data = $request->validate([
            'parcial1'      => 'nullable|numeric|min:0|max:100',
            'parcial2'      => 'nullable|numeric|min:0|max:100',
            'final'         => 'nullable|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $grade->update($data);

        return response()->json($grade);
    }

    // DELETE /grades/{id}
    public function destroy(Grade $grade)
    {
        $grade->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

