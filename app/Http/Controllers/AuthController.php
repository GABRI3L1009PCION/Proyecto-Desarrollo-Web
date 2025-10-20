<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** ============================================
     * 🧩 REGISTRO DE USUARIO (API)
     * ============================================ */
    public function register(Request $r)
    {
        $data = $r->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'in:admin,catedratico,estudiante,secretaria',
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

    /** ============================================
     * 🔐 LOGIN PARA API (devuelve JSON con token)
     * ============================================ */
    public function login(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($r->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user  = Auth::user()->load('branch');
        $token = $user->createToken('api-academia')->accessToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /** ============================================
     * 🌐 LOGIN PARA WEB (redirección según rol)
     * ============================================ */
    public function loginWeb(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($r->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
        }

        $user = Auth::user();

        // 🔁 Redirección según rol (rutas actualizadas)
        switch ($user->role) {
            case 'admin':
                return redirect('/administrador/panel');
            case 'catedratico':
                return redirect('/catedratico/panel');
            case 'estudiante':
                return redirect('/estudiante/panel');
            case 'secretaria':
                return redirect('/secretaria/panel');
            default:
                Auth::logout();
                return redirect('/login')->withErrors(['role' => 'Rol no válido']);
        }
    }

    /** ============================================
     * 👤 USUARIO AUTENTICADO (API)
     * ============================================ */
    public function me(Request $r)
    {
        return response()->json($r->user()->load('branch'));
    }

    /** ============================================
     * 🚪 LOGOUT (API y Web)
     * ============================================ */
    public function logout(Request $r)
    {
        // Si es API (token)
        if ($r->user() && $r->user()->token()) {
            $r->user()->token()->revoke();
            return response()->json(['message' => 'Sesión cerrada']);
        }

        // Si es Web (sesión)
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/login');
    }
}
