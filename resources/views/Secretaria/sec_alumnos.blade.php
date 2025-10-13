@extends('layouts.app')
@section('title', 'Gestión de Alumnos | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/secretaria_alumnos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_cursos_modal.css') }}">
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
                <li class="menu-item">
                    <a href="{{ route('secretaria.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a>
                </li>
                <li class="menu-item active">
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
                <h2><i class="fa-solid fa-user-graduate"></i> Gestión de Alumnos</h2>
                <div class="user-info">
                    <p class="welcome">Bienvenida, <strong>{{ Auth::user()->name ?? 'Secretaria' }}</strong></p>
                </div>
            </header>

            <section class="dashboard">
                <!-- === BARRA DE ACCIONES === -->
                <div class="actions-bar">
                    <button id="btnNuevoAlumno" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Nuevo Alumno
                    </button>

                    <div class="search-box">
                        <input type="text" id="searchAlumno" placeholder="Buscar alumno...">
                        <i class="fa-solid fa-search"></i>
                    </div>
                </div>

                <!-- === TABLA DE ALUMNOS === -->
                <div class="table-section">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Grado</th>
                            <th>Nivel</th>
                            <th>Sucursal</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($alumnos ?? [] as $alumno)
                            <tr>
                                <td>{{ $alumno->nombres }}</td>
                                <td>{{ $alumno->telefono ?? '—' }}</td>
                                <td>{{ $alumno->user->email ?? '—' }}</td>
                                <td>{{ $alumno->grade ?? '—' }}</td>
                                <td>{{ $alumno->level ?? '—' }}</td>
                                <td>{{ $alumno->branch->nombre ?? '—' }}</td>
                                <td class="actions">
                                    <button class="btn-edit"
                                            data-id="{{ $alumno->id }}"
                                            data-nombres="{{ $alumno->nombres }}"
                                            data-telefono="{{ $alumno->telefono }}"
                                            data-grade="{{ $alumno->grade }}"
                                            data-level="{{ $alumno->level }}"
                                            data-branch="{{ $alumno->branch_id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('secretaria.alumnos.destroy', $alumno->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este alumno?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No hay alumnos registrados</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVO ALUMNO === -->
    <div id="modalNuevoAlumno" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-plus"></i> Registrar Alumno</h3>
            <form id="formNuevoAlumno" method="POST" action="{{ route('secretaria.alumnos.store') }}">
                @csrf
                <div class="form-grid">
                    <input type="text" name="nombres" placeholder="Nombre completo" required>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <input type="text" name="telefono" placeholder="Teléfono">
                    <select name="grade" required>
                        <option value="">Seleccione grado...</option>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
                    <select name="level" required>
                        <option value="">Seleccione nivel...</option>
                        <option value="Principiantes I">Principiantes I</option>
                        <option value="Principiantes II">Principiantes II</option>
                        <option value="Avanzados I">Avanzados I</option>
                        <option value="Avanzados II">Avanzados II</option>
                    </select>
                    <select name="branch_id" required>
                        <option value="">Seleccione sucursal...</option>
                        @foreach($sucursales ?? [] as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Guardar</button>
                    <button type="button" id="cerrarModalNuevo" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR ALUMNO === -->
    <div id="modalEditarAlumno" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen-to-square"></i> Editar Alumno</h3>
            <form id="formEditarAlumno" method="POST">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <input type="text" name="nombres" id="editNombres" placeholder="Nombre completo" required>
                    <input type="text" name="telefono" id="editTelefono" placeholder="Teléfono">
                    <select name="grade" id="editGrade" required>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
                    <select name="level" id="editLevel" required>
                        <option value="Principiantes I">Principiantes I</option>
                        <option value="Principiantes II">Principiantes II</option>
                        <option value="Avanzados I">Avanzados I</option>
                        <option value="Avanzados II">Avanzados II</option>
                    </select>
                    <select name="branch_id" id="editBranch" required>
                        @foreach($sucursales ?? [] as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Guardar cambios</button>
                    <button type="button" id="cerrarModalEditar" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === MODAL NUEVO ALUMNO ===
        const modalNuevo = document.getElementById('modalNuevoAlumno');
        document.getElementById('btnNuevoAlumno').addEventListener('click', () => modalNuevo.classList.add('show'));
        document.getElementById('cerrarModalNuevo').addEventListener('click', () => modalNuevo.classList.remove('show'));

        // === MODAL EDITAR ALUMNO ===
        const modalEditar = document.getElementById('modalEditarAlumno');
        const cerrarEditar = document.getElementById('cerrarModalEditar');
        const formEditar = document.getElementById('formEditarAlumno');

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                formEditar.action = `/secretaria/alumnos/${btn.dataset.id}`;
                document.getElementById('editNombres').value = btn.dataset.nombres;
                document.getElementById('editTelefono').value = btn.dataset.telefono;
                document.getElementById('editGrade').value = btn.dataset.grade;
                document.getElementById('editLevel').value = btn.dataset.level;
                document.getElementById('editBranch').value = btn.dataset.branch;
                modalEditar.classList.add('show');
            });
        });

        cerrarEditar.addEventListener('click', () => modalEditar.classList.remove('show'));
    </script>
@endsection
