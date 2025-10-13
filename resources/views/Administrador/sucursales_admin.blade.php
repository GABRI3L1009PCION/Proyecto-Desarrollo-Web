@extends('layouts.app')
@section('title', 'Gestión de Sucursales | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_sucursales.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_users_modal.css') }}">
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
                <li class="menu-item active"><a href="{{ route('administrador.sucursales') }}"><i class="fa-solid fa-building"></i> <span>Sucursales</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.cursos') }}"><i class="fa-solid fa-book-open-reader"></i> <span>Cursos</span></a></li>
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
                <h2><i class="fa-solid fa-building"></i> Gestión de Sucursales</h2>
                <p>Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <section class="sucursales-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de sucursales registradas</h3>
                    <div class="actions-right">
                        <input type="text" placeholder="Buscar sucursal..." id="buscarSucursal">
                        <select id="ordenSucursales">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguas">Más antiguas</option>
                            <option value="alfabetico">A-Z</option>
                            <option value="inverso">Z-A</option>
                        </select>
                        <button class="btn-new" id="btnNuevaSucursal">
                            <i class="fa-solid fa-plus"></i> Nueva sucursal
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Fecha de registro</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaSucursales">
                    @forelse($branches as $branch)
                        <tr>
                            <td>{{ $branch->id }}</td>
                            <td class="nombre">{{ $branch->nombre }}</td>
                            <td>{{ $branch->direccion ?? '—' }}</td>
                            <td>{{ $branch->telefono ?? '—' }}</td>
                            <td>{{ $branch->created_at?->format('Y-m-d') ?? '—' }}</td>
                            <td class="acciones">
                                <button class="btn-edit"
                                        data-id="{{ $branch->id }}"
                                        data-nombre="{{ $branch->nombre }}"
                                        data-direccion="{{ $branch->direccion }}"
                                        data-telefono="{{ $branch->telefono }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-delete" data-id="{{ $branch->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--muted);">No hay sucursales registradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVA SUCURSAL === -->
    <div id="modalNuevaSucursal" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-building-circle-plus"></i> Nueva Sucursal</h3>
            <form method="POST" action="{{ route('administrador.sucursales.store') }}" class="form-modal">
                @csrf
                <label>Nombre de la sucursal</label>
                <input type="text" name="nombre" required>
                <label>Dirección</label>
                <input type="text" name="direccion">
                <label>Teléfono</label>
                <input type="text" name="telefono">
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevaSucursal')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR SUCURSAL === -->
    <div id="modalEditarSucursal" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen"></i> Editar Sucursal</h3>
            <form method="POST" id="formEditarSucursal" class="form-modal">
                @csrf
                @method('PUT')
                <label>Nombre de la sucursal</label>
                <input type="text" name="nombre" id="editNombre" required>
                <label>Dirección</label>
                <input type="text" name="direccion" id="editDireccion">
                <label>Teléfono</label>
                <input type="text" name="telefono" id="editTelefono">
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarSucursal')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR SUCURSAL === -->
    <div id="modalEliminarSucursal" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar esta sucursal? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarSucursal">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarSucursal')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === NUEVA SUCURSAL ===
        const modalNueva = document.getElementById('modalNuevaSucursal');
        const btnNueva = document.getElementById('btnNuevaSucursal');
        btnNueva.addEventListener('click', () => modalNueva.classList.add('show'));

        // === EDITAR ===
        const modalEditar = document.getElementById('modalEditarSucursal');
        const formEditar = document.getElementById('formEditarSucursal');
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.action = `/administrador/sucursales/${id}`;
                document.getElementById('editNombre').value = btn.dataset.nombre;
                document.getElementById('editDireccion').value = btn.dataset.direccion;
                document.getElementById('editTelefono').value = btn.dataset.telefono;
                modalEditar.classList.add('show');
            });
        });

        // === ELIMINAR ===
        const modalEliminar = document.getElementById('modalEliminarSucursal');
        const formEliminar = document.getElementById('formEliminarSucursal');
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = `/administrador/sucursales/${id}`;
                modalEliminar.classList.add('show');
            });
        });

        // === CERRAR MODALES ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') [modalNueva, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });
        function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); }));

        // === BUSCADOR Y ORDEN EN TIEMPO REAL ===
        const buscarSucursal = document.getElementById('buscarSucursal');
        const ordenSucursales = document.getElementById('ordenSucursales');
        const tablaSucursales = document.getElementById('tablaSucursales');

        buscarSucursal.addEventListener('keyup', filtrarYOrdenar);
        ordenSucursales.addEventListener('change', filtrarYOrdenar);

        function filtrarYOrdenar() {
            const texto = buscarSucursal.value.toLowerCase();
            const orden = ordenSucursales.value;
            const filas = Array.from(tablaSucursales.querySelectorAll('tr'));

            // Filtrar por nombre o dirección
            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase() || '';
                const direccion = fila.cells[2]?.textContent.toLowerCase() || '';
                fila.style.display =
                    nombre.includes(texto) || direccion.includes(texto)
                        ? ''
                        : 'none';
            });

            // Ordenar las filas visibles
            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const idA = parseInt(a.querySelector('td').textContent.trim());
                const idB = parseInt(b.querySelector('td').textContent.trim());

                if (orden === 'alfabetico') {
                    const nA = a.querySelector('.nombre').textContent.toLowerCase();
                    const nB = b.querySelector('.nombre').textContent.toLowerCase();
                    return nA.localeCompare(nB);
                }
                if (orden === 'inverso') {
                    const nA = a.querySelector('.nombre').textContent.toLowerCase();
                    const nB = b.querySelector('.nombre').textContent.toLowerCase();
                    return nB.localeCompare(nA);
                }
                if (orden === 'antiguas') return idA - idB;
                if (orden === 'recientes') return idB - idA;

                return 0;
            });

            visibles.forEach(f => tablaSucursales.appendChild(f));
        }
    </script>
@endsection
