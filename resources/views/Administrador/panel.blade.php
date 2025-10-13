@extends('layouts.app')
@section('title', 'Panel del Administrador | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
            </div>

            <ul class="menu">
                <li class="menu-item {{ request()->routeIs('administrador.panel') ? 'active' : '' }}">
                    <a href="{{ route('administrador.panel') }}">
                        <i class="fa-solid fa-gauge"></i> <span>Inicio</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.usuarios') ? 'active' : '' }}">
                    <a href="{{ route('administrador.usuarios') }}">
                        <i class="fa-solid fa-user-gear"></i> <span>Usuarios</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.sucursales') ? 'active' : '' }}">
                    <a href="{{ route('administrador.sucursales') }}">
                        <i class="fa-solid fa-building"></i> <span>Sucursales</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.alumnos') ? 'active' : '' }}">
                    <a href="{{ route('administrador.alumnos') }}">
                        <i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.catedraticos') ? 'active' : '' }}">
                    <a href="{{ route('administrador.catedraticos') }}">
                        <i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.cursos') ? 'active' : '' }}">
                    <a href="{{ route('administrador.cursos') }}">
                        <i class="fa-solid fa-book-open-reader"></i> <span>Cursos</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('administrador.reportes') ? 'active' : '' }}">
                    <a href="{{ route('administrador.reportes') }}">
                        <i class="fa-solid fa-chart-line"></i> <span>Reportes</span>
                    </a>
                </li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2>Panel del Administrador</h2>
                <p>Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === DISTRIBUCIÓN PRINCIPAL === -->
            <div class="dashboard-grid">
                <!-- IZQUIERDA: GRÁFICO DE ROLES -->
                <div class="chart-box">
                    <h3><i class="fa-solid fa-chart-pie"></i> Distribución de Roles</h3>
                    <canvas id="rolesChart"></canvas>
                </div>

                <!-- DERECHA: TARJETAS -->
                <div class="stats-box">
                    <div class="card">
                        <div class="icon"><i class="fa-solid fa-users"></i></div>
                        <h3>Alumnos</h3>
                        <p>{{ \App\Models\User::where('role', 'estudiante')->count() }}</p>
                    </div>
                    <div class="card">
                        <div class="icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                        <h3>Catedráticos</h3>
                        <p>{{ \App\Models\User::where('role', 'catedratico')->count() }}</p>
                    </div>
                    <div class="card">
                        <div class="icon"><i class="fa-solid fa-building"></i></div>
                        <h3>Sucursales</h3>
                        <p>{{ \App\Models\Branch::count() }}</p>
                    </div>
                    <div class="card">
                        <div class="icon"><i class="fa-solid fa-book"></i></div>
                        <h3>Cursos</h3>
                        <p>{{ \App\Models\Course::count() }}</p>
                    </div>
                </div>
            </div>

            <!-- === ÚLTIMOS REGISTROS === -->
            <section class="recent">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Últimos alumnos registrados</h3>
                <ul>
                    @forelse(\App\Models\User::where('role','estudiante')->latest()->take(5)->get() as $alumno)
                        <li><i class="fa-solid fa-user-graduate"></i> {{ $alumno->name }} — {{ $alumno->email }}</li>
                    @empty
                        <li>No hay alumnos registrados aún.</li>
                    @endforelse
                </ul>
            </section>
        </main>
    </div>

    <!-- === SCRIPT CHART.JS === -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('rolesChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Estudiantes', 'Catedráticos', 'Administradores', 'Secretarias'],
                datasets: [{
                    data: [
                        {{ \App\Models\User::where('role','estudiante')->count() }},
                        {{ \App\Models\User::where('role','catedratico')->count() }},
                        {{ \App\Models\User::where('role','admin')->count() }},
                        {{ \App\Models\User::where('role','secretaria')->count() }}
                    ],
                    backgroundColor: ['#3B6DFF', '#FF5C5C', '#3CCF91', '#FFC94A'],
                    borderWidth: 2,
                    borderColor: '#081A3B'
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#ffffff',
                            padding: 15,
                            font: { size: 13 }
                        }
                    }
                }
            }
        });
    </script>
@endsection
