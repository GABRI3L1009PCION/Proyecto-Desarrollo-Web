<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Registro de usuario */
    public function register(Request $r)
    {
        $data = $r->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'in:admin,catedratico,estudiante,secretaria', // opcional
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'] ?? 'estudiante',
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user
        ], 201);
    }

    /** Login */
    public function login(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($r->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user  = Auth::user()->load('branch'); // incluye branch
        $token = $user->createToken('api-academia')->accessToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /** Usuario autenticado */
    public function me(Request $r)
    {
        return response()->json($r->user()->load('branch'));
    }

    /** Logout */
    public function logout(Request $r)
    {
        $r->user()->token()->revoke();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
