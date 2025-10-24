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
                        <!-- 📊 Tipo de reporte -->
                        <select id="tipoReporte">
                            <option value="inscritos">Alumnos inscritos</option>
                            <option value="grado">Por grado</option>
                            <option value="nivel">Por nivel</option>
                            <option value="notas">Notas por curso y grado</option>
                            <option value="sucursal">Listado de alumnos por sucursal</option>
                            <option value="estadisticas">Estadísticas por grado</option>
                        </select>

                        <!-- 🎯 Filtros específicos -->
                        <select id="filtroCurso" style="display:none;"></select>
                        <select id="filtroGrado" style="display:none;"></select>
                        <select id="filtroSucursal" style="display:none;"></select>

                        <!-- 🗓️ Fechas -->
                        <input type="date" id="fechaInicio">
                        <input type="date" id="fechaFin">

                        <button class="btn-new" id="btnGenerar"><i class="fa-solid fa-play"></i> Generar</button>
                        <button class="btn-excel" id="btnExportar"><i class="fa-solid fa-file-excel"></i> Exportar</button>
                    </div>
                </div>

                <!-- 📋 Tabla dinámica -->
                <div class="table-wrapper">
                    <table class="data-table" id="tablaReportes">
                        <thead></thead>
                        <tbody>
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--muted);">
                                Selecciona un tipo de reporte y filtros para comenzar.
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
        const filtroCurso = document.getElementById('filtroCurso');
        const filtroGrado = document.getElementById('filtroGrado');
        const filtroSucursal = document.getElementById('filtroSucursal');

        // === Mostrar / ocultar filtros según el tipo ===
        tipo.addEventListener('change', async () => {
            filtroCurso.style.display = 'none';
            filtroGrado.style.display = 'none';
            filtroSucursal.style.display = 'none';
            inicio.disabled = fin.disabled = false;
            inicio.style.opacity = fin.style.opacity = '1';

            if (tipo.value === 'notas') {
                filtroCurso.style.display = 'inline-block';
                filtroGrado.style.display = 'inline-block';
                await cargarCursosYGrados();
            } else if (tipo.value === 'sucursal') {
                filtroSucursal.style.display = 'inline-block';
                await cargarSucursales();
                inicio.disabled = fin.disabled = true;
                inicio.style.opacity = fin.style.opacity = '0.5';
            } else if (['grado', 'nivel', 'estadisticas'].includes(tipo.value)) {
                inicio.disabled = fin.disabled = true;
                inicio.style.opacity = fin.style.opacity = '0.5';
            }
        });

        // === Cargar cursos y grados reales ===
        async function cargarCursosYGrados() {
            try {
                const res = await fetch("/administrador/data/cursos");
                const cursos = await res.json();
                filtroCurso.innerHTML = '<option value="">Todos los cursos</option>' +
                    cursos.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            } catch {
                filtroCurso.innerHTML = '<option value="">Error al cargar cursos</option>';
            }

            const grados = ['Novatos', 'Expertos'];
            filtroGrado.innerHTML = '<option value="">Todos los grados</option>' +
                grados.map(g => `<option value="${g}">${g}</option>`).join('');
        }

        // === Cargar sucursales ===
        async function cargarSucursales() {
            try {
                const res = await fetch("/administrador/data/sucursales");
                const sucursales = await res.json();
                filtroSucursal.innerHTML = '<option value="">Todas las sucursales</option>' +
                    sucursales.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
            } catch {
                filtroSucursal.innerHTML = '<option value="">Error al cargar sucursales</option>';
            }
        }

        // === Generar reporte (AJAX) ===
        btnGenerar.addEventListener('click', async () => {
            const t = tipo.value;
            const fi = inicio.value;
            const ff = fin.value;
            const cursoId = filtroCurso.value;
            const grado = filtroGrado.value;
            const sucursal = filtroSucursal.value;

            tabla.querySelector('tbody').innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--muted);">Cargando datos...</td></tr>`;

            let url = '';
            if (t === 'inscritos') url = "{{ route('administrador.reportes.inscritos') }}";
            else if (t === 'grado') url = "{{ route('administrador.reportes.grado') }}";
            else if (t === 'nivel') url = "{{ route('administrador.reportes.nivel') }}";
            else if (t === 'notas') url = "{{ route('administrador.reportes.notas') }}";
            else if (t === 'sucursal') url = "{{ route('administrador.reportes.alumnosSucursal') }}";
            else if (t === 'estadisticas') url = "{{ route('administrador.reportes.estadisticas') }}";
            try {
                const params = new URLSearchParams({ fechaInicio: fi, fechaFin: ff, cursoId, grado, branch_id: sucursal });
                const res = await fetch(`${url}?${params.toString()}`);
                const data = await res.json();
                renderTable(t, data);
            } catch {
                tabla.querySelector('tbody').innerHTML = `<tr><td colspan="8" style="text-align:center;color:#e74c3c;">Error al cargar los datos</td></tr>`;
            }
        });

        // === Exportar a Excel ===
        // === Exportar a Excel (enviando TODOS los filtros) ===
        btnExportar.addEventListener('click', () => {
            const t = tipo.value;
            const fi = inicio.value;
            const ff = fin.value;
            const cursoId = filtroCurso.value;
            const grado = filtroGrado.value;
            const sucursal = filtroSucursal.value;

            let url = '';

            if (t === 'inscritos') url = "{{ route('administrador.reportes.export.inscritos') }}";
            else if (t === 'grado' || t === 'nivel') url = "{{ route('administrador.reportes.export.gradoNivel') }}";
            else if (t === 'notas') url = "{{ route('administrador.reportes.export.notas') }}";
            else if (t === 'sucursal') url = "{{ route('administrador.reportes.export.sucursal') }}";
            else if (t === 'estadisticas') url = "{{ route('administrador.reportes.export.estadisticas') }}";

            // ✅ Ahora se incluyen TODOS los filtros
            const params = new URLSearchParams({
                fechaInicio: fi,
                fechaFin: ff,
                cursoId,
                grado,
                branch_id: sucursal
            });

            window.location.href = `${url}?${params.toString()}`;
        });


        // === Renderizar tabla ===
        function renderTable(tipo, data) {
            let head = '', body = '';

            if (tipo === 'inscritos') {
                head = `<tr><th>#</th><th>Alumno</th><th>Curso</th><th>Sucursal</th><th>Fecha</th><th>Estado</th></tr>`;
                data.forEach((i, idx) => body += `<tr><td>${idx+1}</td><td>${i.alumno}</td><td>${i.curso}</td><td>${i.sucursal}</td><td>${i.fecha}</td><td>${i.estado}</td></tr>`);
            }

            if (tipo === 'grado') {
                head = `<tr><th>Grado</th><th>Total de alumnos</th></tr>`;
                data.forEach(i => body += `<tr><td>${i.grade}</td><td>${i.total}</td></tr>`);
            }

            if (tipo === 'nivel') {
                head = `<tr><th>Nivel</th><th>Total de alumnos</th></tr>`;
                data.forEach(i => body += `<tr><td>${i.level}</td><td>${i.total}</td></tr>`);
            }

            if (tipo === 'notas') {
                head = `<tr><th>#</th><th>Curso</th><th>Grado</th><th>Nivel</th><th>Alumno</th><th>P1</th><th>P2</th><th>Final</th><th>Total</th><th>Estado</th><th>Fecha</th></tr>`;
                data.forEach((i, idx) => body += `<tr><td>${idx+1}</td><td>${i.curso}</td><td>${i.grado}</td><td>${i.nivel}</td><td>${i.alumno}</td><td>${i.parcial1}</td><td>${i.parcial2}</td><td>${i.final}</td><td>${i.total}</td><td>${i.estado}</td><td>${i.fecha ?? '-'}</td></tr>`);
            }

            if (tipo === 'sucursal') {
                head = `<tr><th>#</th><th>Alumno</th><th>Grado</th><th>Nivel</th><th>Sucursal</th></tr>`;
                data.forEach((i, idx) => body += `<tr><td>${idx+1}</td><td>${i.alumno}</td><td>${i.grado}</td><td>${i.nivel}</td><td>${i.sucursal}</td></tr>`);
            }

            if (tipo === 'estadisticas') {
                head = `<tr><th>Grado</th><th>Promedio General</th></tr>`;
                data.forEach(i => body += `<tr><td>${i.grade}</td><td>${parseFloat(i.promedio).toFixed(2)}</td></tr>`);
            }

            if (!data || data.length === 0)
                body = `<tr><td colspan="8" style="text-align:center;color:var(--muted);">Sin resultados</td></tr>`;

            tabla.querySelector('thead').innerHTML = head;
            tabla.querySelector('tbody').innerHTML = body;
        }
    </script>
@endsection
