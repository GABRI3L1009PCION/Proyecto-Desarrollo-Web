<?php
namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index() { return Student::with('branch')->latest('id')->get(); }

    public function store(Request $r) {
        $data = $r->validate([
            'branch_id'        => 'required|exists:branches,id',
            'nombres'          => 'required|string|max:120',
            'email'            => 'required|email|unique:students,email',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
        ]);
        return response()->json(Student::create($data), 201);
    }

    public function show(Student $student) { return $student->load('branch'); }

    public function update(Request $r, Student $student) {
        $data = $r->validate([
            'branch_id'        => 'sometimes|required|exists:branches,id',
            'nombres'          => 'sometimes|required|string|max:120',
            'email'            => "sometimes|required|email|unique:students,email,{$student->id}",
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
        ]);
        $student->update($data);
        return $student;
    }

    public function destroy(Student $student) {
        $student->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
