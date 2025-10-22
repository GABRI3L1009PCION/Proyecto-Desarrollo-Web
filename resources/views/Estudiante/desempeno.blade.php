@extends('layouts.app')
@section('title', 'Desempeño | Estudiante | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/estudiante_cursos.css') }}">
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
                <li class="menu-item active"><a href="{{ route('estudiante.desempeno') }}"><i class="fa-solid fa-chart-line"></i> <span>Desempeño</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-chart-line"></i> Desempeño Académico</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === RESUMEN GENERAL === -->
            <section class="summary-section">
                <div class="summary-card">
                    <i class="fa-solid fa-star"></i>
                    <div>
                        <h4>Promedio General</h4>
                        <p>{{ number_format($promedioGeneral, 2) }}%</p>
                    </div>
                </div>

                <div class="summary-card aprobado">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        <h4>Aprobados</h4>
                        <p>{{ $aprobados }} / {{ $totalCursos }}</p>
                    </div>
                </div>

                <div class="summary-card recuperacion">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <div>
                        <h4>Recuperación</h4>
                        <p>{{ $recuperacion }}</p>
                    </div>
                </div>

                <div class="summary-card reprobado">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div>
                        <h4>Reprobados</h4>
                        <p>{{ $reprobados }}</p>
                    </div>
                </div>
            </section>

            <!-- === TABLA DETALLE === -->
            <section class="table-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Detalle por Curso</h3>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Curso</th>
                        <th>Catedrático</th>
                        <th>Puntaje Total</th>
                        <th>Estado</th>
                        <th>Progreso</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($inscripciones as $i => $insc)
                        @php
                            $grade = $insc->grade;
                            $p1 = $grade->parcial1 ?? 0;
                            $p2 = $grade->parcial2 ?? 0;
                            $final = $grade->final ?? 0;
                            $total = $p1 + $p2 + $final;

                            if ($total >= 70) $estado = 'Aprobado';
                            elseif ($total >= 60) $estado = 'Recuperacion';
                            else $estado = 'Reprobado';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $insc->offering->course->nombre }}</td>
                            <td>{{ $insc->offering->teacher->user->name ?? '—' }}</td>
                            <td>{{ number_format($total, 2) }} / 100</td>
                            <td>
                                <span class="estado
                                    {{ strtolower($estado) === 'aprobado' ? 'aprobado' :
                                       (strtolower($estado) === 'recuperacion' ? 'recuperacion' : 'reprobado') }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td style="width: 250px;">
                                <div class="progress-container small">
                                    <div class="progress-bar
                                        {{ strtolower($estado) === 'aprobado' ? 'bar-aprobado' :
                                           (strtolower($estado) === 'recuperacion' ? 'bar-recuperacion' : 'bar-reprobado') }}"
                                         style="width: {{ max(min($total, 100), 0) }}%;">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--muted);">
                                No hay cursos registrados aún.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = width; }, 250);
        });
    </script>
@endsection
