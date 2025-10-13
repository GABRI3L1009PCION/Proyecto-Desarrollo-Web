<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecretariaAlumnoController extends Controller
{
    // 📘 Mostrar lista de alumnos
    public function index()
    {
        // Trae alumnos con su sucursal y usuario (para mostrar correo)
        $alumnos = Student::with(['branch', 'user'])->orderBy('nombres')->get();
        $sucursales = Branch::orderBy('nombre')->get();

        return view('Secretaria.sec_alumnos', compact('alumnos', 'sucursales'));
    }

    // ➕ Registrar nuevo alumno (con usuario)
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:30',
            'grade' => 'required|string',
            'level' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'email' => 'required|email|unique:users,email'
        ]);

        // Crear usuario vinculado al alumno
        $user = User::create([
            'name' => $request->nombres,
            'email' => $request->email,
            'password' => Hash::make('123456'), // Contraseña por defecto
            'role' => 'estudiante'
        ]);

        // Crear el registro de alumno
        Student::create([
            'user_id' => $user->id,
            'branch_id' => $request->branch_id,
            'nombres' => $request->nombres,
            'telefono' => $request->telefono,
            'grade' => $request->grade,
            'level' => $request->level,
        ]);

        return redirect()->route('secretaria.alumnos')
            ->with('success', 'Alumno y usuario creados correctamente.');
    }

    // ✏️ Actualizar alumno
    public function update(Request $request, $id)
    {
        $alumno = Student::findOrFail($id);

        $request->validate([
            'nombres' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:30',
            'grade' => 'required|string',
            'level' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $alumno->update([
            'nombres' => $request->nombres,
            'telefono' => $request->telefono,
            'grade' => $request->grade,
            'level' => $request->level,
            'branch_id' => $request->branch_id,
        ]);

        // También actualiza el nombre del usuario vinculado
        if ($alumno->user) {
            $alumno->user->update(['name' => $request->nombres]);
        }

        return redirect()->route('secretaria.alumnos')
            ->with('success', 'Datos del alumno actualizados correctamente.');
    }

    // 🗑️ Eliminar alumno
    public function destroy($id)
    {
        $alumno = Student::with('user')->findOrFail($id);

        // Eliminar usuario vinculado si existe
        if ($alumno->user) {
            $alumno->user->delete();
        }

        $alumno->delete();

        return redirect()->route('secretaria.alumnos')
            ->with('success', 'Alumno y usuario eliminados correctamente.');
    }
}
