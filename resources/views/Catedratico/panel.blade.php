@extends('layouts.app')
@section('title', 'Panel del Catedrático | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/catedratico_panel.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Catedrático</p>
            </div>

            <ul class="menu">
                <li class="menu-item active"><a href="{{ route('catedratico.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
                <li class="menu-item"><a href="{{ route('catedratico.cursos') }}"><i class="fa-solid fa-book"></i> <span>Mis Cursos</span></a></li>
                <li class="menu-item"><a href="{{ route('catedratico.calificaciones') }}"><i class="fa-solid fa-pen-to-square"></i> <span>Calificaciones</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-gauge"></i> Panel del Catedrático</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === TARJETAS === -->
            <section class="stats-grid">
                <div class="card">
                    <i class="fa-solid fa-book"></i>
                    <h4>Cursos asignados</h4>
                    <p>{{ $totalCursos }}</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-user-graduate"></i>
                    <h4>Alumnos inscritos</h4>
                    <p>{{ $totalAlumnos }}</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-chart-line"></i>
                    <h4>Promedio general</h4>
                    <p>{{ $promedioGeneral }}</p>
                </div>
            </section>

            <!-- === TABLA CURSOS === -->
            <section class="table-section">
                <h3><i class="fa-solid fa-list"></i> Mis cursos asignados</h3>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Curso</th>
                        <th>Grado</th>
                        <th>Nivel</th>
                        <th>Sucursal</th>
                        <th>Año</th>
                        <th>Ciclo</th>
                        <th>Cupo</th>
                        <th>Alumnos</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($cursos as $curso)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $curso->course->nombre }}</td>
                            <td>{{ $curso->grade }}</td>
                            <td>{{ $curso->level }}</td>
                            <td>{{ $curso->branch->nombre ?? '—' }}</td>
                            <td>{{ $curso->anio }}</td>
                            <td>{{ $curso->ciclo }}</td>
                            <td>{{ $curso->cupo }}</td>
                            <td>{{ $curso->enrollments->count() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--muted);">No tienes cursos asignados actualmente.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>
@endsection
