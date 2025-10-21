@extends('layouts.app')
@section('title', 'Mis Cursos | Estudiante | Código Rapidito')

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
                <li class="menu-item active"><a href="{{ route('estudiante.cursos') }}"><i class="fa-solid fa-book-open"></i> <span>Mis Cursos</span></a></li>
                <li class="menu-item"><a href="{{ route('estudiante.desempeno') }}"><i class="fa-solid fa-chart-line"></i> <span>Desempeño</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-book-open"></i> Mis Cursos</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === TABLA DE CURSOS === -->
            <section class="table-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de cursos inscritos</h3>
                    <div class="actions-right">
                        <input type="text" id="buscarCurso" placeholder="Buscar curso o catedrático...">
                    </div>
                </div>

                <table class="data-table" id="tablaCursos">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Curso</th>
                        <th>Catedrático</th>
                        <th>Nivel</th>
                        <th>Grado</th>
                        <th>Sucursal</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tbodyCursos">
                    @foreach($inscripciones as $i => $insc)
                        @php
                            $grade = $insc->grade;
                            $p1 = $grade->parcial1 ?? 0;
                            $p2 = $grade->parcial2 ?? 0;
                            $final = $grade->final ?? 0;
                            $total = $p1 + $p2 + $final;
                            $porcentaje = round($total, 2);
                            $estado = $grade?->estado ?? (
                                $porcentaje >= 60 ? 'Aprobado' :
                                ($porcentaje == 0 ? 'Pendiente' : 'Reprobado')
                            );
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $insc->offering->course->nombre }}</td>
                            <td>{{ $insc->offering->teacher->user->name ?? '—' }}</td>
                            <td>{{ $insc->offering->level }}</td>
                            <td>{{ $insc->offering->grade }}</td>
                            <td>{{ $insc->offering->branch->nombre ?? '—' }}</td>
                            <td>
                                <button class="btn-ver" onclick="abrirModal('modalCurso{{ $i }}')">
                                    <i class="fa-solid fa-eye"></i> Ver notas
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <!-- === MODALES DE NOTAS (FUERA DEL TBODY) === -->
                @foreach($inscripciones as $i => $insc)
                    @php
                        $grade = $insc->grade;
                        $p1 = $grade->parcial1 ?? 0;
                        $p2 = $grade->parcial2 ?? 0;
                        $final = $grade->final ?? 0;
                        $total = $p1 + $p2 + $final;
                        $porcentaje = round($total, 2);
                        $estado = $grade?->estado ?? (
                            $porcentaje >= 60 ? 'Aprobado' :
                            ($porcentaje == 0 ? 'Pendiente' : 'Reprobado')
                        );
                    @endphp
                    <div id="modalCurso{{ $i }}" class="modal-overlay">
                        <div class="modal-content modal-notas">
                            <h3><i class="fa-solid fa-clipboard-list"></i> {{ $insc->offering->course->nombre }}</h3>
                            <p style="color: var(--muted); margin-bottom: 1rem;">
                                Catedrático: <strong>{{ $insc->offering->teacher->user->name ?? '—' }}</strong>
                            </p>

                            <table class="data-table">
                                <thead>
                                <tr>
                                    <th>Parcial 1 (30 pts)</th>
                                    <th>Parcial 2 (30 pts)</th>
                                    <th>Examen Final (40 pts)</th>
                                    <th>Puntaje Total</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{ number_format($p1, 2) }}</td>
                                    <td>{{ number_format($p2, 2) }}</td>
                                    <td>{{ number_format($final, 2) }}</td>
                                    <td>{{ number_format($porcentaje, 2) }} / 100</td>
                                    <td>
                                        <span class="estado
                                            {{ strtolower($estado) === 'aprobado' ? 'aprobado' :
                                               (strtolower($estado) === 'reprobado' ? 'reprobado' : 'pendiente') }}">
                                            {{ $estado }}
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div class="progress-container">
                                <div class="progress-bar
                                {{ strtolower($estado) === 'aprobado' ? 'bar-aprobado' :
                                   (strtolower($estado) === 'reprobado' ? 'bar-reprobado' : 'bar-pendiente') }}"
                                     style="width: {{ max(min($porcentaje, 100), 0) }}%;">
                                </div>
                                <span class="progress-label">{{ $porcentaje }}%</span>
                            </div>

                            <div class="modal-actions">
                                <button type="button" class="btn-cancel" onclick="cerrarModal('modalCurso{{ $i }}')">
                                    <i class="fa-solid fa-xmark"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        </main>
    </div>

    <!-- === SCRIPTS === -->
    <script>
        document.getElementById('buscarCurso').addEventListener('input', function () {
            const filtro = this.value.toLowerCase();
            document.querySelectorAll('#tbodyCursos tr').forEach(tr => {
                tr.style.display = tr.innerText.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });

        // === MODALES ===
        function abrirModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('show');
            animarBarra(id);
        }
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('show');
        }
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
            }
        });
        document.querySelectorAll('.modal-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); })
        );

        // === ANIMACIÓN DE BARRA ===
        function animarBarra(id) {
            const modal = document.getElementById(id);
            const bar = modal.querySelector('.progress-bar');
            const finalWidth = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => { bar.style.width = finalWidth; }, 200);
        }
    </script>
@endsection
