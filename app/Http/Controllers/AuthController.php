<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r){
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json($user, 201);
    }

    public function login(Request $r){
        $r->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($r->only('email','password'))) {
            return response()->json(['message'=>'Credenciales inválidas'], 401);
        }
        $token = Auth::user()->createToken('api-academia')->accessToken;
        return response()->json(['user'=>Auth::user(),'token'=>$token]);
    }

    public function me(Request $r){ return $r->user(); }

    public function logout(Request $r){
        $r->user()->token()->revoke();
        return response()->json(['message'=>'Sesión cerrada']);
    }
}
