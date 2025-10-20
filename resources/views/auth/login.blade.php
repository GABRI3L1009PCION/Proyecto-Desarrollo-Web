@extends('layouts.app')
@section('title', 'Iniciar Sesión | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/login.css') }}">

    <div class="login-container">
        <!-- 🌟 Logo animado -->
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Código Rapidito" class="logo">
        </div>

        <!-- 🌌 Partículas decorativas -->
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>

        <!-- 🪟 Tarjeta de login -->
        <div class="card">
            <h3>INICIAR SESIÓN</h3>

            <!-- Formulario normal (usa loginWeb del AuthController) -->
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email">CORREO ELECTRÓNICO</label>
                    <input type="email" name="email" id="email" required autocomplete="off"
                           value="{{ old('email') }}">
                </div>

                <div class="mb-3">
                    <label for="password">CONTRASEÑA</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <!-- Mensaje de error si las credenciales son incorrectas -->
                @if ($errors->any())
                    <div class="alert alert-danger text-center" style="font-size: 0.9rem;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="btn">INGRESAR</button>

                <p class="text-center">
                    ¿NO TIENES UNA CUENTA?
                    <a href="{{ url('/register') }}">REGÍSTRATE</a>
                </p>
            </form>
        </div>
    </div>
@endsection
