@extends('layouts.app')
@section('title', 'Gestión de Cursos | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_cursos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_cursos_modal.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
            </div>

            <ul class="menu">
                <li class="menu-item"><a href="{{ route('administrador.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Inicio</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.usuarios') }}"><i class="fa-solid fa-user-gear"></i> <span>Usuarios</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.sucursales') }}"><i class="fa-solid fa-building"></i> <span>Sucursales</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
                <li class="menu-item active"><a href="{{ route('administrador.cursos') }}"><i class="fa-solid fa-book-open-reader"></i> <span>Cursos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.reportes') }}"><i class="fa-solid fa-chart-line"></i> <span>Reportes</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-book"></i> Gestión de Cursos</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <section class="cursos-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de cursos registrados</h3>
                    <div class="actions-right">
                        <input type="text" placeholder="Buscar curso..." id="buscarCurso">
                        <select id="ordenCursos">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="alfabetico">A-Z</option>
                            <option value="inverso">Z-A</option>
                        </select>
                        <button class="btn-new" id="btnNuevoCurso">
                            <i class="fa-solid fa-plus"></i> Nuevo curso
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Créditos</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaCursos">
                    @forelse($courses as $course)
                        <tr>
                            <td>{{ $course->id }}</td>
                            <td>{{ $course->codigo }}</td>
                            <td class="nombre">{{ $course->nombre }}</td>
                            <td>{{ $course->creditos ?? '—' }}</td>
                            <td>{{ $course->descripcion ?? '—' }}</td>
                            <td class="acciones">
                                <button class="btn-edit"
                                        data-id="{{ $course->id }}"
                                        data-codigo="{{ $course->codigo }}"
                                        data-nombre="{{ $course->nombre }}"
                                        data-creditos="{{ $course->creditos }}"
                                        data-descripcion="{{ $course->descripcion }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-delete" data-id="{{ $course->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No hay cursos registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVO CURSO === -->
    <div id="modalNuevoCurso" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-plus"></i> Nuevo Curso</h3>
            <form method="POST" action="{{ route('administrador.cursos.store') }}" class="form-modal">
                @csrf
                <div>
                    <label>Código</label>
                    <input type="text" name="codigo" required>
                </div>
                <div>
                    <label>Nombre del curso</label>
                    <input type="text" name="nombre" required>
                </div>
                <div>
                    <label>Créditos</label>
                    <input type="number" name="creditos" min="0" max="20">
                </div>
                <div class="campo-full">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevoCurso')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR CURSO === -->
    <div id="modalEditarCurso" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen"></i> Editar Curso</h3>
            <form method="POST" id="formEditarCurso" class="form-modal">
                @csrf
                @method('PUT')
                <div>
                    <label>Código</label>
                    <input type="text" name="codigo" id="editCodigo" required>
                </div>
                <div>
                    <label>Nombre del curso</label>
                    <input type="text" name="nombre" id="editNombre" required>
                </div>
                <div>
                    <label>Créditos</label>
                    <input type="number" name="creditos" id="editCreditos" min="0" max="20">
                </div>
                <div class="campo-full">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="editDescripcion" rows="3"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarCurso')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR CURSO === -->
    <div id="modalEliminarCurso" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este curso? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarCurso">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarCurso')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === NUEVO CURSO ===
        const modalNuevo = document.getElementById('modalNuevoCurso');
        document.getElementById('btnNuevoCurso').addEventListener('click', () => modalNuevo.classList.add('show'));

        // === EDITAR ===
        const modalEditar = document.getElementById('modalEditarCurso');
        const formEditar = document.getElementById('formEditarCurso');
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.action = /administrador/cursos/${id};
                document.getElementById('editCodigo').value = btn.dataset.codigo;
                document.getElementById('editNombre').value = btn.dataset.nombre;
                document.getElementById('editCreditos').value = btn.dataset.creditos;
                document.getElementById('editDescripcion').value = btn.dataset.descripcion;
                modalEditar.classList.add('show');
            });
        });

        // === ELIMINAR ===
        const modalEliminar = document.getElementById('modalEliminarCurso');
        const formEliminar = document.getElementById('formEliminarCurso');
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = /administrador/cursos/${id};
                modalEliminar.classList.add('show');
            });
        });

        // === BUSCAR Y ORDENAR ===
        const buscar = document.getElementById('buscarCurso');
        const orden = document.getElementById('ordenCursos');

        [buscar, orden].forEach(el => el.addEventListener('input', filtrarYOrdenar));

        function filtrarYOrdenar() {
            const texto = buscar.value.toLowerCase();
            const ordenSel = orden.value;
            const filas = Array.from(document.querySelectorAll('#tablaCursos tr'));

            // 🔍 Filtro por nombre
            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase();
                fila.style.display = (!texto || nombre.includes(texto)) ? '' : 'none';
            });

            // 🔄 Ordenamiento correcto
            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const nA = a.querySelector('.nombre').textContent.toLowerCase();
                const nB = b.querySelector('.nombre').textContent.toLowerCase();
                const idA = parseInt(a.children[0].textContent);
                const idB = parseInt(b.children[0].textContent);
                if (ordenSel === 'alfabetico') return nA.localeCompare(nB);
                if (ordenSel === 'inverso') return nB.localeCompare(nA);
                if (ordenSel === 'antiguos') return idA - idB;
                if (ordenSel === 'recientes') return idB - idA;
                return 0;
            });

            const tbody = document.getElementById('tablaCursos');
            visibles.forEach(f => tbody.appendChild(f));
        }

        // === CERRAR MODALES ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') [modalNuevo, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });
        function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); })
        );
    </script>
@endsection
