@extends('layouts.app')
@section('title', 'Perfil | Estudiante | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/estudiante_perfil.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Estudiante</p>
            </div>

            <ul class="menu">
                <li class="menu-item"><a href="{{ route('estudiante.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
                <li class="menu-item"><a href="{{ route('estudiante.cursos') }}"><i class="fa-solid fa-book-open"></i> <span>Mis Cursos</span></a></li>
                <li class="menu-item"><a href="{{ route('estudiante.desempeno') }}"><i class="fa-solid fa-chart-line"></i> <span>Desempeño</span></a></li>
                <li class="menu-item active"><a href="{{ route('estudiante.perfil') }}"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-id-card"></i> Mi Perfil</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === PERFIL GENERAL === -->
            <section class="perfil-section">
                <div class="perfil-card">
                    <div class="perfil-header">
                        <img src="{{ asset('images/avatar_student.png') }}" alt="Avatar" class="avatar">
                        <div>
                            <h3>{{ $student->user->name ?? 'Estudiante' }}</h3>
                            <p class="rol">Estudiante de {{ $student->branch->nombre ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="perfil-info">
                        <div class="info-item">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <span>Correo</span>
                                <p>{{ $student->user->email ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <span>Teléfono</span>
                                <p>{{ $student->telefono ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <div>
                                <span>Dirección</span>
                                <p>{{ $student->direccion ?? 'No registrada' }}</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <div>
                                <span>DPI</span>
                                <p>{{ $student->dpi ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fa-solid fa-calendar"></i>
                            <div>
                                <span>Miembro desde</span>
                                <p>{{ $student->created_at?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection
