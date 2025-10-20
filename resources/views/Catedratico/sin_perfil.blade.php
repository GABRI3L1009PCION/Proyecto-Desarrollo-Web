@extends('layouts.app')
@section('title', 'Perfil pendiente de activación | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/cate_noperfil_panel.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Catedrático</p>
            </div>

            <ul class="menu">
                <li class="menu-item active"><a href="#"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-circle-info"></i> Perfil pendiente</h2>
                <p class="welcome">Bienvenido, <strong>{{ $user->name }}</strong></p>
            </header>

            <section class="empty-profile">
                <div class="info-box">
                    <i class="fa-solid fa-user-clock"></i>
                    <h3>Tu perfil de catedrático aún no está activado</h3>
                    <p>
                        Has ingresado correctamente al sistema, pero todavía no has sido asignado
                        como catedrático por la administración.
                        <br><br>
                        Una vez que tu cuenta sea vinculada a un registro en la base de datos de docentes,
                        podrás acceder a tus cursos y alumnos.
                    </p>
                </div>
            </section>
        </main>
    </div>
@endsection
