<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeacherController extends Controller
{
    // GET /teachers
    public function index()
    {
        return response()->json(Teacher::with('user', 'branch')->paginate(10));
    }

    // POST /teachers
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'   => 'required|exists:users,id|unique:teachers,user_id',
            'branch_id' => 'required|exists:branches,id',
            'nombres'   => 'nullable|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $teacher = Teacher::create($data);

        return response()->json($teacher, Response::HTTP_CREATED);
    }

    // GET /teachers/{id}
    public function show(Teacher $teacher)
    {
        return response()->json($teacher->load('user', 'branch'));
    }

    // PUT/PATCH /teachers/{id}
    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'branch_id' => 'sometimes|exists:branches,id',
            'nombres'   => 'nullable|string|max:150',
            'telefono'  => 'nullable|string|max:30',
        ]);

        $teacher->update($data);

        return response()->json($teacher);
    }

    // DELETE /teachers/{id}
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
