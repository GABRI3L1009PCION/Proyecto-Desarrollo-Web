@extends('layouts.app')
@section('title', 'Mis Cursos | Catedrático | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/catedratico_cursos.css') }}">
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
                <li class="menu-item"><a href="{{ route('catedratico.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
                <li class="menu-item active"><a href="{{ route('catedratico.cursos') }}"><i class="fa-solid fa-book"></i> <span>Mis Cursos</span></a></li>
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
                <h2><i class="fa-solid fa-book"></i> Mis Cursos</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === TABLA DE CURSOS === -->
            <section class="table-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de cursos asignados</h3>
                    <div class="actions-right">

                        <!-- === BUSCADOR (ahora primero) === -->
                        <input type="text" id="buscarCurso" placeholder="Buscar curso, grado o nivel...">

                        <!-- === NUEVOS FILTROS === -->
                        <select id="filtroGrado">
                            <option value="">Grado</option>
                            @foreach($cursos->pluck('grade')->unique() as $grado)
                                <option value="{{ $grado }}">{{ $grado }}</option>
                            @endforeach
                        </select>

                        <select id="filtroNivel">
                            <option value="">Nivel</option>
                            @foreach($cursos->pluck('level')->unique() as $nivel)
                                <option value="{{ $nivel }}">{{ $nivel }}</option>
                            @endforeach
                        </select>

                        <select id="filtroCiclo">
                            <option value="">Ciclo</option>
                            @foreach($cursos->pluck('ciclo')->unique() as $ciclo)
                                <option value="{{ $ciclo }}">{{ $ciclo }}</option>
                            @endforeach
                        </select>

                        <select id="filtroCupo">
                            <option value="">Cupo</option>
                            @foreach($cursos->pluck('cupo')->unique() as $cupo)
                                <option value="{{ $cupo }}">{{ $cupo }}</option>
                            @endforeach
                        </select>

                        <!-- === ORDEN === -->
                        <select id="ordenCursos">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="alfabetico">A - Z</option>
                            <option value="inverso">Z - A</option>
                            <option value="alumnos_desc">Más alumnos → menos</option>
                            <option value="alumnos_asc">Menos alumnos → más</option>
                        </select>
                    </div>
                </div>

                <table class="data-table" id="tablaCursos">
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
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tbodyCursos">
                    @forelse($cursos as $curso)
                        <tr
                            data-offering="{{ $curso->id }}"
                            data-anio="{{ $curso->anio }}"
                            data-alumnos="{{ $curso->enrollments->count() }}"
                        >
                            <td>{{ $loop->iteration }}</td>
                            <td class="nombre-curso">{{ $curso->course->nombre }}</td>
                            <td>{{ $curso->grade }}</td>
                            <td>{{ $curso->level }}</td>
                            <td>{{ $curso->branch->nombre ?? '—' }}</td>
                            <td>{{ $curso->anio }}</td>
                            <td>{{ $curso->ciclo }}</td>
                            <td>{{ $curso->cupo }}</td>
                            <td class="num-alumnos">{{ $curso->enrollments->count() }}</td>
                            <td>
                                <button class="btn-ver" onclick="abrirModal('modalCurso{{ $curso->id }}')">
                                    Ver alumnos
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;color:var(--muted);">
                                No tienes cursos asignados actualmente.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </section>

            <!-- === MODALES DE ALUMNOS === -->
            @foreach($cursos as $curso)
                <div id="modalCurso{{ $curso->id }}" class="modal-overlay">
                    <div class="modal-content modal-alumnos">
                        <h3><i class="fa-solid fa-users"></i> Alumnos inscritos en {{ $curso->course->nombre }}</h3>
                        <p style="color:var(--muted);margin-bottom:10px;">Total: {{ $curso->enrollments->count() }} alumnos</p>
                        <input type="text" id="buscarAlumno{{ $curso->id }}" class="filtro-modal" placeholder="Buscar alumno...">

                        <div class="tabla-alumnos">
                            <table class="data-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                    <th>Grado</th>
                                    <th>Nivel</th>
                                </tr>
                                </thead>
                                <tbody id="tablaAlumnos{{ $curso->id }}">
                                @forelse($curso->enrollments as $index => $inscripcion)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $inscripcion->student->user->name ?? 'Sin nombre' }}</td>
                                        <td>{{ $inscripcion->student->telefono ?? '—' }}</td>
                                        <td>{{ $curso->grade }}</td>
                                        <td>{{ $curso->level }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center;color:var(--muted);">
                                            No hay alumnos inscritos en este curso.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn-cancel" onclick="cerrarModal('modalCurso{{ $curso->id }}')">Cerrar</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </main>
    </div>

    <!-- === SCRIPT PARA FILTROS === -->
    <script>
        // === BUSCADOR GLOBAL ===
        document.getElementById('buscarCurso').addEventListener('input', function () {
            const filtro = this.value.toLowerCase();
            document.querySelectorAll('#tbodyCursos tr[data-offering]').forEach(tr => {
                tr.style.display = tr.innerText.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });

        // === ORDENAR CURSOS ===
        document.getElementById('ordenCursos').addEventListener('change', function () {
            const tbody = document.getElementById('tbodyCursos');
            const filas = Array.from(tbody.querySelectorAll('tr[data-offering]'));
            const valor = this.value;

            filas.sort((a, b) => {
                const idA = parseInt(a.dataset.offering) || 0;
                const idB = parseInt(b.dataset.offering) || 0;
                const alumnosA = parseInt(a.dataset.alumnos) || 0;
                const alumnosB = parseInt(b.dataset.alumnos) || 0;
                const nombreA = (a.querySelector('.nombre-curso')?.textContent || '').trim().toLowerCase();
                const nombreB = (b.querySelector('.nombre-curso')?.textContent || '').trim().toLowerCase();

                switch (valor) {
                    case 'recientes': return idB - idA;
                    case 'antiguos': return idA - idB;
                    case 'alfabetico': return nombreA.localeCompare(nombreB);
                    case 'inverso': return nombreB.localeCompare(nombreA);
                    case 'alumnos_desc': return alumnosB - alumnosA;
                    case 'alumnos_asc': return alumnosA - alumnosB;
                    default: return 0;
                }
            });

            filas.forEach(f => tbody.appendChild(f));
        });

        // === FILTROS MULTIPLES ===
        const filtros = ['filtroGrado', 'filtroNivel', 'filtroCiclo', 'filtroCupo'];
        filtros.forEach(id => document.getElementById(id).addEventListener('change', aplicarFiltros));

        function aplicarFiltros() {
            const grado = document.getElementById('filtroGrado').value.toLowerCase();
            const nivel = document.getElementById('filtroNivel').value.toLowerCase();
            const ciclo = document.getElementById('filtroCiclo').value.toLowerCase();
            const cupo = document.getElementById('filtroCupo').value.toLowerCase();

            document.querySelectorAll('#tbodyCursos tr[data-offering]').forEach(tr => {
                const tdGrado = tr.children[2].innerText.toLowerCase();
                const tdNivel = tr.children[3].innerText.toLowerCase();
                const tdCiclo = tr.children[6].innerText.toLowerCase();
                const tdCupo = tr.children[7].innerText.toLowerCase();

                const coincideGrado = !grado || tdGrado === grado;
                const coincideNivel = !nivel || tdNivel === nivel;
                const coincideCiclo = !ciclo || tdCiclo === ciclo;
                const coincideCupo = !cupo || tdCupo === cupo;

                tr.style.display = (coincideGrado && coincideNivel && coincideCiclo && coincideCupo) ? '' : 'none';
            });
        }

        // === FILTROS DE ALUMNOS EN MODAL ===
        @foreach($cursos as $curso)
        document.getElementById('buscarAlumno{{ $curso->id }}').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            document.querySelectorAll('#tablaAlumnos{{ $curso->id }} tr').forEach(tr => {
                tr.style.display = tr.innerText.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });
        @endforeach

        // === MODALES ===
        function abrirModal(id) {
            document.getElementById(id).classList.add('show');
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
    </script>
@endsection
