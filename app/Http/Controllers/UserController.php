<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response; // Para usar constantes HTTP
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Para reglas de validación

class UserController extends Controller
{
    /** ======================================================
     * API: Listar usuarios con filtros, orden y paginación
     * ====================================================== */
    public function index(Request $request)
    {
        $request->validate([
            'q'    => 'nullable|string|max:50', // Término de búsqueda
            'role' => ['nullable', Rule::in(['admin', 'catedratico', 'estudiante', 'secretaria'])], // Filtro de rol
            'sort' => ['nullable', Rule::in(['recientes', 'antiguos', 'alfabetico', 'inverso'])], // Orden
            'page' => 'nullable|integer|min:1', // Página
        ]);

        $query = User::query();

        // Aplicar filtro de búsqueda (nombre o email)
        if ($q = $request->input('q')) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Aplicar filtro de rol
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Aplicar orden
        $sort = $request->input('sort', 'recientes'); // Por defecto, recientes
        switch ($sort) {
            case 'antiguos':
                $query->orderBy('id', 'asc');
                break;
            case 'alfabetico':
                $query->orderBy('name', 'asc');
                break;
            case 'inverso':
                $query->orderBy('name', 'desc');
                break;
            case 'recientes':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        // Paginar resultados
        $users = $query->paginate(15); // 15 por página

        return response()->json([
            'success' => true,
            'message' => '📋 Lista de usuarios obtenida.',
            'count'   => $users->total(),
            'data'    => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(), // Incluir total aquí también es útil
            ]
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * API: Crear un nuevo usuario
     * ====================================================== */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100', // Solo letras y espacios
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', // Mayús, minús, número
            'role'     => ['required', Rule::in(['admin', 'catedratico', 'estudiante', 'secretaria'])],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Usuario creado exitosamente.',
            'data'    => $user
        ], Response::HTTP_CREATED); // 201 Created
    }

    /** ======================================================
     * API: Ver detalles de un usuario (opcional, pero útil)
     * ====================================================== */
    public function show(User $user)
    {
        // Podrías cargar relaciones si fuera necesario, ej: ->load('studentProfile', 'teacherProfile')
        return response()->json([
            'success' => true,
            'message' => '👀 Detalles del usuario obtenidos.',
            'data'    => $user
        ], Response::HTTP_OK);
    }


    /** ======================================================
     * API: Actualizar un usuario existente
     * ====================================================== */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/|max:100',
            'email' => ['sometimes','required','email', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes','required', Rule::in(['admin', 'catedratico', 'estudiante', 'secretaria'])],
            // Contraseña opcional en actualización
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ]);

        // Actualiza los datos principales
        $user->fill([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'role' => $data['role'] ?? $user->role,
        ]);

        // Actualiza la contraseña SOLO si se proporcionó una nueva
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => '✏️ Usuario actualizado correctamente.',
            'data'    => $user
        ], Response::HTTP_OK);
    }

    /** ======================================================
     * API: Eliminar un usuario (con validaciones)
     * ====================================================== */
    public function destroy(User $user)
    {
        // 1. Evitar que el admin se elimine a sí mismo
        if (auth()->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => '❌ No puedes eliminar tu propio usuario.',
            ], Response::HTTP_CONFLICT); // 409 Conflict
        }

        // 2. Verificar dependencias (si es estudiante o catedrático)
        // Asumiendo que tienes relaciones 'student' y 'teacher' en el modelo User
        if ($user->role === 'estudiante' && $user->student()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ No se puede eliminar: el usuario tiene un perfil de estudiante asociado.',
            ], Response::HTTP_CONFLICT);
        }
        if ($user->role === 'catedratico' && $user->teacher()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ No se puede eliminar: el usuario tiene un perfil de catedrático asociado.',
            ], Response::HTTP_CONFLICT);
        }

        // Si pasa las validaciones, eliminar
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => '🗑️ Usuario eliminado correctamente.'
        ], Response::HTTP_OK); // O 204 No Content si prefieres no devolver cuerpo
    }
}
