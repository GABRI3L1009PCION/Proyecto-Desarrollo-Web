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
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
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
                <input type="text"
                       name="telefono"
                       maxlength="8"
                       inputmode="numeric"
                       pattern="[0-9]{8}"
                       required>
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
                <input type="text"
                       name="telefono"
                       id="editTelefono"
                       maxlength="8"
                       inputmode="numeric"
                       pattern="[0-9]{8}"
                       required>

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
        // === MODALES ===
        const modalNueva = document.getElementById('modalNuevaSucursal');
        const modalEditar = document.getElementById('modalEditarSucursal');
        const modalEliminar = document.getElementById('modalEliminarSucursal');
        const btnNueva = document.getElementById('btnNuevaSucursal');

        btnNueva.addEventListener('click', () => modalNueva.classList.add('show'));

        // === ALERTA FLOTANTE ===
        function showFloatingAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = alert ${type};
            alert.textContent = message;
            Object.assign(alert.style, {
                position: 'fixed',
                top: '15px',
                left: '50%',
                transform: 'translateX(-50%)',
                padding: '10px 20px',
                borderRadius: '8px',
                zIndex: '9999',
                background: type === 'error' ? '#e74c3c' : '#2ecc71',
                color: '#fff',
                boxShadow: '0 4px 10px rgba(0,0,0,0.3)',
                opacity: '0',
                transition: 'opacity 0.3s ease'
            });
            document.body.appendChild(alert);
            setTimeout(() => alert.style.opacity = '1', 50);
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 400);
            }, 3500);
        }

        // === VALIDACIÓN SOLO PARA TELÉFONO ===
        const telefonoRegex = /^[0-9]{8}$/;

        // === FORM NUEVA SUCURSAL ===
        const formNueva = document.querySelector('#modalNuevaSucursal form');
        formNueva.setAttribute('novalidate', true);

        formNueva.addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;
            const nombre = form.nombre.value.trim();
            const direccion = form.direccion.value.trim();
            const telefono = form.telefono.value.trim();

            if (!telefonoRegex.test(telefono))
                return showFloatingAlert('❌ El teléfono debe tener exactamente 8 dígitos numéricos.');

            const data = { nombre, direccion, telefono };

            try {
                const res = await fetch("{{ route('administrador.sucursales.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    showFloatingAlert('✅ Sucursal creada correctamente.', 'success');
                    cerrarModal('modalNuevaSucursal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const err = await res.json();
                    showFloatingAlert(err.message || '❌ Error al crear la sucursal.');
                }
            } catch {
                showFloatingAlert('⚠ Error de conexión con el servidor.');
            }
        });

        // === FORM EDITAR SUCURSAL ===
        const formEditar = document.getElementById('formEditarSucursal');
        formEditar.setAttribute('novalidate', true);

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.setAttribute('data-id', id);
                document.getElementById('editNombre').value = btn.dataset.nombre;
                document.getElementById('editDireccion').value = btn.dataset.direccion;
                document.getElementById('editTelefono').value = btn.dataset.telefono;
                modalEditar.classList.add('show');
            });
        });

        formEditar.addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;
            const id = form.getAttribute('data-id');
            const nombre = form.nombre.value.trim();
            const direccion = form.direccion.value.trim();
            const telefono = form.telefono.value.trim();

            if (!telefonoRegex.test(telefono))
                return showFloatingAlert('❌ El teléfono debe tener exactamente 8 dígitos numéricos.');

            const data = { nombre, direccion, telefono, _method: 'PUT' };

            try {
                const res = await fetch(/administrador/sucursales/${id}, {
                    method: 'POST', // usamos POST + _method PUT
                        headers: {
                        'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    showFloatingAlert('✅ Sucursal actualizada correctamente.', 'success');
                    cerrarModal('modalEditarSucursal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const err = await res.json();
                    showFloatingAlert(err.message || '❌ Error al actualizar la sucursal.');
                }
            } catch {
                showFloatingAlert('⚠ Error de conexión con el servidor.');
            }
        });

        // === ELIMINAR SUCURSAL ===
        const formEliminar = document.getElementById('formEliminarSucursal');
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = /administrador/sucursales/${id};
                modalEliminar.classList.add('show');
            });
        });

        // === CERRAR MODALES ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') [modalNueva, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });
        function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); })
        );

        // === BUSCADOR Y ORDEN ===
        const buscarSucursal = document.getElementById('buscarSucursal');
        const ordenSucursales = document.getElementById('ordenSucursales');
        const tablaSucursales = document.getElementById('tablaSucursales');

        buscarSucursal.addEventListener('keyup', filtrarYOrdenar);
        ordenSucursales.addEventListener('change', filtrarYOrdenar);

        function filtrarYOrdenar() {
            const texto = buscarSucursal.value.toLowerCase();
            const orden = ordenSucursales.value;
            const filas = Array.from(tablaSucursales.querySelectorAll('tr'));

            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase() || '';
                const direccion = fila.cells[2]?.textContent.toLowerCase() || '';
                fila.style.display = nombre.includes(texto) || direccion.includes(texto) ? '' : 'none';
            });

            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const idA = parseInt(a.querySelector('td').textContent.trim());
                const idB = parseInt(b.querySelector('td').textContent.trim());
                if (orden === 'alfabetico')
                    return a.querySelector('.nombre').textContent.localeCompare(b.querySelector('.nombre').textContent);
                if (orden === 'inverso')
                    return b.querySelector('.nombre').textContent.localeCompare(a.querySelector('.nombre').textContent);
                if (orden === 'antiguas') return idA - idB;
                if (orden === 'recientes') return idB - idA;
                return 0;
            });

            visibles.forEach(f => tablaSucursales.appendChild(f));
        }
    </script>

@endsection
