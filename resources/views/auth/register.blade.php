@extends('layouts.app')
@section('title', 'Registro | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/register.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <!-- === PARTÍCULAS DECORATIVAS === -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <!-- === CONTENEDOR PRINCIPAL === -->
    <div class="register-container">
        <div class="logo-box">
            <img src="{{ asset('images/logo.png') }}" alt="Código Rapidito" class="logo">
        </div>

        <div class="card">
            <h3>CREA TU CUENTA</h3>

            <!-- === FORMULARIO DE REGISTRO === -->
            <form id="registerForm" class="form-grid">@csrf

                <!-- 🔹 Fila 1: Nombre y correo -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">NOMBRE COMPLETO</label>
                        <input type="text" id="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">CORREO ELECTRÓNICO</label>
                        <input type="email" id="email" required>
                    </div>
                </div>

                <!-- 🔹 Fila 2: Contraseñas más anchas -->
                <div class="form-row full">
                    <div class="form-group">
                        <label for="password">CONTRASEÑA</label>
                        <input type="password" id="password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">CONFIRMAR CONTRASEÑA</label>
                        <input type="password" id="password_confirmation" required>
                    </div>
                </div>

                <!-- 🔹 Botón -->
                <button class="btn">REGISTRARME</button>

                <p class="text-center mt-3">
                    ¿YA TIENES UNA CUENTA?
                    <a href="{{ url('/login') }}">INICIA SESIÓN</a>
                </p>
            </form>

            <div id="registerMessage" class="msg"></div>
        </div>
    </div>

    <!-- === SCRIPT DE REGISTRO === -->
    <script>
        document.getElementById('registerForm').addEventListener('submit', async e => {
            e.preventDefault();
            const msg = document.getElementById('registerMessage');
            msg.style.color = 'var(--text-muted)';
            msg.textContent = 'Registrando...';

            const data = {
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };

            try {
                const res = await fetch("{{ url('api/v1/auth/register') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();
                if (res.ok) {
                    msg.style.color = 'var(--success)';
                    msg.textContent = 'Registro exitoso. Redirigiendo...';
                    setTimeout(() => window.location.href = '/login', 1500);
                } else {
                    msg.style.color = 'var(--error)';
                    msg.textContent = json.message || 'Error en el registro';
                }
            } catch (err) {
                msg.style.color = 'var(--error)';
                msg.textContent = 'Error de conexión con el servidor.';
            }
        });
    </script>
@endsection
