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
            <form id="loginForm">@csrf
                <div class="mb-3">
                    <label for="email">CORREO ELECTRÓNICO</label>
                    <input type="email" id="email" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password">CONTRASEÑA</label>
                    <input type="password" id="password" required>
                </div>
                <button class="btn">INGRESAR</button>
                <p class="text-center">¿NO TIENES UNA CUENTA? <a href="{{ url('/register') }}">REGÍSTRATE</a></p>
            </form>
            <div id="loginMessage" class="msg"></div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async e=>{
            e.preventDefault();
            const email=document.getElementById('email').value.trim();
            const password=document.getElementById('password').value.trim();
            const msg=document.getElementById('loginMessage');
            msg.textContent='Verificando...';
            try{
                const res=await fetch("{{ url('api/v1/auth/login') }}",{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json'},
                    body:JSON.stringify({email,password})
                });
                const data=await res.json();
                if(res.ok){
                    localStorage.setItem('token',data.token);
                    window.location.href='/dashboard';
                }else msg.textContent=data.message||'Credenciales inválidas';
            }catch{
                msg.textContent='Error de conexión con el servidor.';
            }
        });
    </script>
@endsection
