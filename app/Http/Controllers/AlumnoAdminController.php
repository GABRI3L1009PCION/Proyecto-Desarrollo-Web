<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Branch;
use Illuminate\Http\Request;

class AlumnoAdminController extends Controller
{
    /**
     * 📋 Mostrar lista de alumnos (usuarios con rol estudiante)
     */
    public function index()
    {
        $students = Student::with(['branch', 'user'])
            ->orderByDesc('id')
            ->get();

        // Usuarios con rol estudiante sin registro en students (para vincularlos)
        $usuariosNoRegistrados = User::where('role', 'estudiante')
            ->whereDoesntHave('student')
            ->get();

        $branches = Branch::orderBy('nombre')->get();

        return view('Administrador.alumnos_admin', compact('students', 'branches', 'usuariosNoRegistrados'));
    }

    /**
     * ➕ Registrar un nuevo alumno
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'nombres'          => 'required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'required|in:Novatos,Expertos',
            'level'            => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
            'branch_id'        => 'required|exists:branches,id',
        ]);

        Student::create($validated);

        return redirect()->route('administrador.alumnos')
            ->with('success', '✅ Alumno vinculado y registrado exitosamente.');
    }

    /**
     * ✏️ Editar alumno
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'nombres'          => 'required|string|max:120',
            'telefono'         => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'grade'            => 'required|in:Novatos,Expertos',
            'level'            => 'required|in:Principiantes I,Principiantes II,Avanzados I,Avanzados II',
            'branch_id'        => 'required|exists:branches,id',
        ]);

        $student->update($validated);

        return redirect()->route('administrador.alumnos')
            ->with('success', '✏️ Alumno actualizado correctamente.');
    }

    /**
     * ❌ Eliminar alumno
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('administrador.alumnos')
            ->with('success', '🗑️ Alumno eliminado exitosamente.');
    }
}
