@extends('layouts.app')
@section('title', 'Panel de Estudiante | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/estudiante_panel.css') }}">
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
                <li class="menu-item active"><a href="{{ route('estudiante.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
                <li class="menu-item"><a href="{{ route('estudiante.cursos') }}"><i class="fa-solid fa-book-open"></i> <span>Mis Cursos</span></a></li>
                <li class="menu-item"><a href="{{ route('estudiante.desempeno') }}"><i class="fa-solid fa-chart-line"></i> <span>Desempeño</span></a></li>
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
                <h2><i class="fa-solid fa-graduation-cap"></i> Panel de Estudiante</h2>
                <div class="user-info">
                    <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name ?? 'Estudiante' }}</strong></p>
                </div>
            </header>

            <section class="dashboard">
                <!-- === TARJETAS DE RESUMEN === -->
                @php
                    $aprobados = 0;
                    $recuperacion = 0;
                    $reprobados = 0;
                    $totalNotas = 0;
                    $sumaNotas = 0;

                    foreach ($inscripciones as $i) {
                        if ($i->grade) {
                            $total = ($i->grade->parcial1 ?? 0) + ($i->grade->parcial2 ?? 0) + ($i->grade->final ?? 0);
                            $sumaNotas += $total;
                            $totalNotas++;

                            if ($total >= 70) $aprobados++;
                            elseif ($total >= 60) $recuperacion++;
                            else $reprobados++;
                        }
                    }

                    $promedioGeneral = $totalNotas > 0 ? round($sumaNotas / $totalNotas, 2) : 0;
                @endphp

                <div class="cards-grid">
                    <div class="card promedio">
                        <i class="fa-solid fa-star icon"></i>
                        <h3>{{ $promedioGeneral }}%</h3>
                        <p>Promedio general</p>
                    </div>

                    <div class="card aprobado">
                        <i class="fa-solid fa-circle-check icon"></i>
                        <h3>{{ $aprobados }}</h3>
                        <p>Cursos aprobados</p>
                    </div>

                    <div class="card recuperacion">
                        <i class="fa-solid fa-hourglass-half icon"></i>
                        <h3>{{ $recuperacion }}</h3>
                        <p>En recuperación</p>
                    </div>

                    <div class="card reprobado">
                        <i class="fa-solid fa-circle-xmark icon"></i>
                        <h3>{{ $reprobados }}</h3>
                        <p>Cursos reprobados</p>
                    </div>
                </div>

                <!-- === NOTAS RECIENTES === -->
                <div class="recent-section">
                    <h3><i class="fa-solid fa-bell"></i> Notas recientes</h3>
                    <div class="notifications-list">
                        @php
                            $notasRecientes = $inscripciones->filter(fn($i) => $i->grade)
                                ->sortByDesc(fn($i) => $i->grade->updated_at)
                                ->take(5);
                        @endphp

                        @forelse($notasRecientes as $nota)
                            <div class="notification-item">
                                <i class="fa-solid fa-circle-check icono"></i>
                                <div>
                                    <p><strong>{{ $nota->offering->course->nombre ?? 'Curso desconocido' }}</strong></p>
                                    <span>
                                        Parcial 1: <b>{{ $nota->grade->parcial1 ?? '—' }}</b> |
                                        Parcial 2: <b>{{ $nota->grade->parcial2 ?? '—' }}</b> |
                                        Examen Final: <b>{{ $nota->grade->final ?? '—' }}</b>
                                    </span>
                                    <p class="fecha">
                                        Última actualización: {{ $nota->grade->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="no-notas">No hay notas recientes todavía.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>
    </div>

    <style>
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .card {
            background: rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 1.5rem 1.2rem;
            text-align: center;
            color: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: transform .3s ease, box-shadow .3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        .card i {
            font-size: 2rem;
            margin-bottom: .6rem;
        }
        .card.promedio {
            background: linear-gradient(145deg, rgba(33,150,243,0.15), rgba(25,118,210,0.3));
            border: 1px solid rgba(33,150,243,0.3);
        }
        .card.aprobado {
            background: linear-gradient(145deg, rgba(0,230,118,0.1), rgba(0,200,83,0.25));
            border: 1px solid rgba(0,230,118,0.3);
        }
        .card.recuperacion {
            background: linear-gradient(145deg, rgba(255,235,59,0.1), rgba(255,193,7,0.25));
            border: 1px solid rgba(255,235,59,0.3);
        }
        .card.reprobado {
            background: linear-gradient(145deg, rgba(255,82,82,0.1), rgba(183,28,28,0.25));
            border: 1px solid rgba(255,82,82,0.3);
        }

        /* === NOTAS RECIENTES === */
        .recent-section {
            background: rgba(255,255,255,0.06);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        }
        .recent-section h3 {
            color: var(--primary-light);
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .notification-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: .8rem;
            box-shadow: inset 0 0 6px rgba(255,255,255,0.05);
        }
        .notification-item .icono {
            color: var(--primary-light);
            font-size: 1.3rem;
        }
        .notification-item p {
            margin: 0;
        }
        .notification-item .fecha {
            font-size: .8rem;
            color: var(--muted);
        }
        .no-notas {
            color: var(--muted);
            text-align: center;
            margin-top: 1rem;
        }
    </style>
@endsection
