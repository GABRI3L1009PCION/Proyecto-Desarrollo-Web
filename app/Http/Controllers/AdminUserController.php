<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Mostrar la lista de usuarios
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('Administrador.usuarios_admin', compact('users'));
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return redirect()->back()->with('success', '✅ Usuario creado exitosamente.');
    }

    /**
     * Actualizar un usuario existente
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Si el admin cambia la contraseña manualmente
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->back()->with('success', '✏️ Usuario actualizado correctamente.');
    }

    /**
     * Eliminar un usuario
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Evitar que el admin se elimine a sí mismo
        if (auth()->user()->id === $user->id) {
            return redirect()->back()->with('error', '❌ No puedes eliminar tu propio usuario.');
        }

        $user->delete();
        return redirect()->back()->with('success', '🗑️ Usuario eliminado correctamente.');
    }
}
