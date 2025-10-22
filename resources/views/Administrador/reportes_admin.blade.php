@extends('layouts.app')
@section('title', 'Reportes Académicos | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_reportes.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Administrador</p>
            </div>

            <ul class="menu">
                <li class="menu-item"><a href="{{ route('administrador.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Inicio</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.usuarios') }}"><i class="fa-solid fa-user-gear"></i> <span>Usuarios</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.sucursales') }}"><i class="fa-solid fa-building"></i> <span>Sucursales</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.cursos') }}"><i class="fa-solid fa-book-open-reader"></i> <span>Cursos</span></a></li>
                <li class="menu-item active"><a href="{{ route('administrador.reportes') }}"><i class="fa-solid fa-chart-line"></i> <span>Reportes</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-chart-line"></i> Gestión de Reportes</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <section class="reportes-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-database"></i> Generar Reporte</h3>
                    <div class="actions-right">
                        <select id="tipoReporte">
                            <option value="inscritos">Alumnos inscritos</option>
                            <option value="grado_nivel">Por grado y nivel</option>
                            <option value="notas">Notas por curso</option>
                        </select>
                        <input type="date" id="fechaInicio">
                        <input type="date" id="fechaFin">
                        <button class="btn-new" id="btnGenerar">
                            <i class="fa-solid fa-play"></i> Generar
                        </button>
                        <button class="btn-excel" id="btnExportar">
                            <i class="fa-solid fa-file-excel"></i> Exportar
                        </button>
                    </div>
                </div>

                <!-- Contenedor de tabla dinámica -->
                <div class="table-wrapper">
                    <table class="data-table" id="tablaReportes">
                        <thead></thead>
                        <tbody>
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--muted);">
                                Selecciona un tipo de reporte y rango de fechas para comenzar.
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <style>
        .btn-excel {
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.55rem 1rem;
            margin-left: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s ease;
            display: flex;
            align-items: center;
            gap: .4rem;
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.3);
        }
        .btn-excel:hover {
            background: #27ae60;
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.5);
        }
    </style>

    <script>
        const tipo = document.getElementById('tipoReporte');
        const inicio = document.getElementById('fechaInicio');
        const fin = document.getElementById('fechaFin');
        const btnGenerar = document.getElementById('btnGenerar');
        const btnExportar = document.getElementById('btnExportar');
        const tabla = document.getElementById('tablaReportes');

        // === BLOQUEAR / DESBLOQUEAR FECHAS SEGÚN TIPO DE REPORTE ===
        tipo.addEventListener('change', () => {
            if (tipo.value === 'grado_nivel') {
                inicio.disabled = true;
                fin.disabled = true;
                inicio.value = '';
                fin.value = '';
                inicio.style.opacity = '0.5';
                fin.style.opacity = '0.5';
            } else {
                inicio.disabled = false;
                fin.disabled = false;
                inicio.style.opacity = '1';
                fin.style.opacity = '1';
            }
        });

        // === GENERAR REPORTE (AJAX) ===
        btnGenerar.addEventListener('click', async () => {
            const t = tipo.value;
            const fi = inicio.value;
            const ff = fin.value;

            tabla.querySelector('tbody').innerHTML = `
                <tr><td colspan="8" style="text-align:center;color:var(--muted);">Cargando datos...</td></tr>
            `;

            let url = '';
            if (t === 'inscritos') url = "{{ route('administrador.reportes.inscritos') }}";
            else if (t === 'grado_nivel') url = "{{ route('administrador.reportes.gradoNivel') }}";
            else if (t === 'notas') url = "{{ route('administrador.reportes.notas') }}";

            try {
                const params = new URLSearchParams({ fechaInicio: fi, fechaFin: ff });
                const res = await fetch(`${url}?${params.toString()}`);
                const data = await res.json();
                renderTable(t, data);
            } catch (e) {
                tabla.querySelector('tbody').innerHTML = `
                    <tr><td colspan="8" style="text-align:center;color:#e74c3c;">Error al cargar los datos</td></tr>
                `;
            }
        });

        // === EXPORTAR A EXCEL ===
        btnExportar.addEventListener('click', () => {
            const t = tipo.value;
            const fi = inicio.value;
            const ff = fin.value;

            let url = '';
            if (t === 'inscritos') url = "{{ route('administrador.reportes.export.inscritos') }}";
            else if (t === 'grado_nivel') url = "{{ route('administrador.reportes.export.gradoNivel') }}";
            else if (t === 'notas') url = "{{ route('administrador.reportes.export.notas') }}";

            const params = new URLSearchParams({ fechaInicio: fi, fechaFin: ff });
            window.location.href = `${url}?${params.toString()}`;
        });

        // === FUNCIÓN PARA RENDERIZAR TABLA ===
        function renderTable(tipo, data) {
            let head = '', body = '';

            if (tipo === 'inscritos') {
                head = `<tr><th>#</th><th>Alumno</th><th>Curso</th><th>Sucursal</th><th>Fecha</th><th>Estado</th></tr>`;
                data.forEach((i, idx) => {
                    body += `<tr>
                        <td>${idx + 1}</td>
                        <td>${i.alumno}</td>
                        <td>${i.curso}</td>
                        <td>${i.sucursal}</td>
                        <td>${i.fecha}</td>
                        <td>${i.estado}</td>
                    </tr>`;
                });
            }

            if (tipo === 'grado_nivel') {
                head = `<tr><th>Grado</th><th>Nivel</th><th>Total de alumnos</th></tr>`;
                data.forEach(i => {
                    body += `<tr>
                        <td>${i.grade}</td>
                        <td>${i.level}</td>
                        <td>${i.total}</td>
                    </tr>`;
                });
            }

            if (tipo === 'notas') {
                head = `<tr><th>#</th><th>Curso</th><th>Alumno</th><th>P1</th><th>P2</th><th>Final</th><th>Total</th><th>Estado</th></tr>`;
                data.forEach((i, idx) => {
                    body += `<tr>
                        <td>${idx + 1}</td>
                        <td>${i.curso}</td>
                        <td>${i.alumno}</td>
                        <td>${i.parcial1 ?? '-'}</td>
                        <td>${i.parcial2 ?? '-'}</td>
                        <td>${i.final ?? '-'}</td>
                        <td>${i.total ?? '-'}</td>
                        <td>${i.estado ?? '-'}</td>
                    </tr>`;
                });
            }

            if (data.length === 0) {
                body = `<tr><td colspan="8" style="text-align:center;color:var(--muted);">Sin resultados</td></tr>`;
            }

            tabla.querySelector('thead').innerHTML = head;
            tabla.querySelector('tbody').innerHTML = body;
        }
    </script>
@endsection
