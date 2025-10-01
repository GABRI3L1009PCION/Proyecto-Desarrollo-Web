<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    // GET /courses
    public function index()
    {
        return response()->json(Course::paginate(10));
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

        return response()->json($course, Response::HTTP_CREATED);
    }

    // GET /courses/{id}
    public function show(Course $course)
    {
        return response()->json($course->load('offerings'));
    }

    // PUT/PATCH /courses/{id}
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'codigo'      => 'sometimes|string|max:20|unique:courses,codigo,' . $course->id,
            'nombre'      => 'sometimes|string|max:255',
            'creditos'    => 'nullable|integer|min:0|max:20',
            'descripcion' => 'nullable|string',
        ]);

        $course->update($data);

        return response()->json($course);
    }

    // DELETE /courses/{id}
    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
