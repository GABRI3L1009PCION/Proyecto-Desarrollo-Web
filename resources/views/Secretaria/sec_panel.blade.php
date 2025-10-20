@extends('layouts.app')
@section('title', 'Panel de Secretaría | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/secretaria_panel.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Secretaría</p>
            </div>

            <ul class="menu">
                <li class="menu-item active">
                    <a href="#"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('secretaria.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('secretaria.inscripciones') }}"><i class="fa-solid fa-clipboard-list"></i> <span>Inscripciones</span></a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('secretaria.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('secretaria.reportes') }}"><i class="fa-solid fa-file-lines"></i> <span>Reportes</span></a>
                </li>
            </ul>

            <div class="logout-area">
                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                    </button>
                </form>
            </div>

        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-house"></i> Panel de Secretaría</h2>
                <div class="user-info">
                    <p class="welcome">Bienvenida, <strong>{{ Auth::user()->name ?? 'Secretaria' }}</strong></p>
                </div>
            </header>

            <section class="dashboard">
                <div class="cards-grid">
                    <div class="card">
                        <i class="fa-solid fa-user-graduate icon"></i>
                        <h3>{{ $totalAlumnos ?? 0 }}</h3>
                        <p>Alumnos registrados</p>
                    </div>

                    <div class="card">
                        <i class="fa-solid fa-chalkboard-user icon"></i>
                        <h3>{{ $totalCatedraticos ?? 0 }}</h3>
                        <p>Catedráticos activos</p>
                    </div>

                    <div class="card">
                        <i class="fa-solid fa-book-open icon"></i>
                        <h3>{{ $totalCursos ?? 0 }}</h3>
                        <p>Cursos disponibles</p>
                    </div>

                    <div class="card">
                        <i class="fa-solid fa-clipboard-list icon"></i>
                        <h3>{{ $totalInscripciones ?? 0 }}</h3>
                        <p>Inscripciones realizadas</p>
                    </div>
                </div>

                <div class="recent-section">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Últimas inscripciones</h3>
                    <table class="recent-table">
                        <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Curso</th>
                            <th>Grado</th>
                            <th>Sucursal</th>
                            <th>Fecha</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ultimasInscripciones ?? [] as $ins)
                            <tr>
                            <tr>
                                <td>{{ $ins->student->user->name ?? '—' }}</td>
                                <td>{{ $ins->offering->course->nombre ?? '—' }}</td>
                                <td>{{ $ins->offering->grade ?? '—' }}</td>
                                <td>{{ $ins->offering->branch->nombre ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($ins->fecha)->format('d/m/Y') }}</td>
                            </tr>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">No hay inscripciones recientes</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
@endsection
