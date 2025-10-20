<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Branch;
use Illuminate\Http\Request;

class SecretariaAlumnoController extends Controller
{
    /**
     * 📋 Mostrar lista de alumnos (usuarios con rol estudiante)
     */
    public function index()
    {
        $alumnos = Student::with(['branch', 'user'])
            ->orderByDesc('id')
            ->get();

        // Usuarios con rol estudiante sin registro en students (para vincularlos)
        $usuariosNoRegistrados = User::where('role', 'estudiante')
            ->whereDoesntHave('student')
            ->orderBy('name')
            ->get();

        $branches = Branch::orderBy('nombre')->get();

        return view('Secretaria.alumnos', compact('alumnos', 'branches', 'usuariosNoRegistrados'));
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

        return redirect()->route('secretaria.alumnos')
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

        // 🔹 También actualiza el nombre en la tabla users si el alumno tiene usuario
        if ($student->user) {
            $student->user->update(['name' => $request->nombres]);
        }

        return redirect()->route('secretaria.alumnos')
            ->with('success', '✏️ Alumno actualizado correctamente.');
    }

    /**
     * ❌ Eliminar alumno
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        // 🔸 No se elimina el usuario, solo el vínculo académico
        return redirect()->route('secretaria.alumnos')
            ->with('success', '🗑️ Alumno eliminado exitosamente.');
    }
}
