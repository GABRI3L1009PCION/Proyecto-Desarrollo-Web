@extends('layouts.app')
@section('title', 'Gestión de Usuarios | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dash.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_users.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_users_modal.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <!-- === ALERTAS DE SESIÓN === -->
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

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
                <li class="menu-item active"><a href="{{ route('administrador.usuarios') }}"><i class="fa-solid fa-user-gear"></i> <span>Usuarios</span></a></li>
                <li class="menu-item"><a href="{{ route('administrador.sucursales') }}"><i class="fa-solid fa-building"></i> <span>Sucursales</span></a></li>
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
                <h2><i class="fa-solid fa-user-gear"></i> Gestión de Usuarios</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <section class="users-section">
                <div class="header-actions">
                    <div class="left-group">
                        <h3><i class="fa-solid fa-users"></i> Lista de usuarios registrados</h3>

                        <input type="text" id="buscarUsuario" class="filtro-input" placeholder="Buscar por nombre o correo...">
                        <select id="filtroRol" class="filtro-select">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administrador</option>
                            <option value="catedratico">Catedrático</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="secretaria">Secretaria</option>
                        </select>
                        <select id="filtroOrden" class="filtro-select">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="alfabetico">A-Z</option>
                            <option value="inverso">Z-A</option>
                        </select>
                    </div>

                    <button class="add-btn" id="btnNuevoUsuario">
                        <i class="fa-solid fa-plus"></i> Nuevo usuario
                    </button>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha de registro</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="nombre">{{ $user->name }}</td>
                            <td class="correo">{{ $user->email }}</td>
                            <td><span class="role {{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                            <td class="fecha">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td class="actions">
                                <button class="edit"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->role }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="delete" data-id="{{ $user->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; color:var(--muted);">No hay usuarios registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVO USUARIO === -->
    <div id="modalNuevoUsuario" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-plus"></i> Nuevo Usuario</h3>
            <form method="POST" action="{{ route('administrador.usuarios.store') }}" class="form-modal" id="formNuevoUsuario">
                @csrf
                <label>Nombre completo</label>
                <input type="text" name="name"
                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                       title="Solo se permiten letras y espacios."
                       required>

                <label>Correo electrónico</label>
                <input type="email" name="email"
                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                       title="Ingresa un correo válido (ejemplo@dominio.com)."
                       required>

                <label>Contraseña</label>
                <input type="password" name="password"
                       pattern="^(?=.[a-z])(?=.[A-Z])(?=.*\d).{8,}$"
                       title="Mínimo 8 caracteres, incluyendo mayúscula, minúscula y número."
                       required>
                <small style="color:#aaa; font-size:0.9rem;">
                    La contraseña debe tener al menos 8 caracteres, con mayúscula, minúscula y número.
                </small>

                <label>Rol del usuario</label>
                <select name="role" required>
                    <option value="admin">Administrador</option>
                    <option value="catedratico">Catedrático</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="secretaria">Secretaria</option>
                </select>

                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevoUsuario')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR === -->
    <div id="modalEditarUsuario" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-pen"></i> Editar Usuario</h3>
            <form method="POST" id="formEditarUsuario" class="form-modal">
                @csrf
                @method('PUT')
                <label>Nombre completo</label>
                <input type="text" name="name" id="editName"
                       pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                       title="Solo se permiten letras y espacios."
                       required>
                <label>Correo electrónico</label>
                <input type="email" name="email" id="editEmail"
                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                       title="Ingresa un correo válido (ejemplo@dominio.com)."
                       required>
                <label>Rol del usuario</label>
                <select name="role" id="editRole" required>
                    <option value="admin">Administrador</option>
                    <option value="catedratico">Catedrático</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="secretaria">Secretaria</option>
                </select>
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarUsuario')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR === -->
    <div id="modalEliminarUsuario" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarUsuario">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarUsuario')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === NUEVO USUARIO ===
        const modalNuevo = document.getElementById('modalNuevoUsuario');
        const btnNuevo = document.getElementById('btnNuevoUsuario');
        btnNuevo.addEventListener('click', () => modalNuevo.classList.add('show'));

        // === ALERTA FLOTANTE ===
        function showFloatingAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = `alert ${type}`; // ✅ corregido
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

        // === VALIDACIONES ===
        const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/; // ✅ corregido

        // === FORM NUEVO USUARIO ===
        const formNuevo = document.querySelector('#formNuevoUsuario');
        formNuevo.setAttribute('novalidate', true);

        formNuevo.addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;

            const name = form.name.value.trim();
            const email = form.email.value.trim();
            const password = form.password.value.trim();
            const role = form.role.value;

            if (!nameRegex.test(name))
                return showFloatingAlert('❌ El nombre solo puede contener letras y espacios.');
            if (!emailRegex.test(email))
                return showFloatingAlert('❌ Ingresa un correo electrónico válido (ejemplo@dominio.com).');
            if (!passRegex.test(password))
                return showFloatingAlert('❌ Contraseña inválida (mínimo 8 caracteres, con mayúscula, minúscula y número).');

            const data = { name, email, password, role };

            try {
                const res = await fetch("{{ route('administrador.usuarios.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    showFloatingAlert('✅ Usuario creado correctamente.', 'success');
                    cerrarModal('modalNuevoUsuario');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const err = await res.json();
                    showFloatingAlert(err.message || '❌ Error al crear el usuario.');
                }
            } catch {
                showFloatingAlert('⚠️ Error de conexión con el servidor.');
            }
        });

        // === MODAL EDITAR ===
        const modalEditar = document.getElementById('modalEditarUsuario');
        const formEditar = document.getElementById('formEditarUsuario');

        document.querySelectorAll('.edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.setAttribute('data-id', id);
                document.getElementById('editName').value = btn.dataset.name;
                document.getElementById('editEmail').value = btn.dataset.email;
                document.getElementById('editRole').value = btn.dataset.role;
                modalEditar.classList.add('show');
            });
        });

        formEditar.setAttribute('novalidate', true);

        formEditar.addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;
            const id = form.getAttribute('data-id');
            const name = form.name.value.trim();
            const email = form.email.value.trim();
            const role = form.role.value;

            if (!nameRegex.test(name))
                return showFloatingAlert('❌ El nombre solo puede contener letras y espacios.');
            if (!emailRegex.test(email))
                return showFloatingAlert('❌ Ingresa un correo electrónico válido.');

            const data = { name, email, role, _method: 'PUT' };

            try {
                const res = await fetch(`/administrador/usuarios/${id}`, { // ✅ corregido
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify(data)
                });
                if (res.ok) {
                    showFloatingAlert('✅ Usuario actualizado correctamente.', 'success');
                    cerrarModal('modalEditarUsuario');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const err = await res.json();
                    showFloatingAlert(err.message || '❌ Error al actualizar el usuario.');
                }
            } catch {
                showFloatingAlert('⚠️ Error de conexión con el servidor.');
            }
        });

        // === MODAL ELIMINAR ===
        const modalEliminar = document.getElementById('modalEliminarUsuario');
        const formEliminar = document.getElementById('formEliminarUsuario');
        document.querySelectorAll('.delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = `/administrador/usuarios/${id}`; // ✅ corregido
                modalEliminar.classList.add('show');
            });
        });

        // === CERRAR MODALES ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') [modalNuevo, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });
        function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); })
        );

        // === BUSCADOR Y FILTROS ===
        const buscar = document.getElementById('buscarUsuario');
        const filtroRol = document.getElementById('filtroRol');
        const filtroOrden = document.getElementById('filtroOrden');

        buscar.addEventListener('keyup', filtrarYOrdenar);
        filtroRol.addEventListener('change', filtrarYOrdenar);
        filtroOrden.addEventListener('change', filtrarYOrdenar);

        function filtrarYOrdenar() {
            const texto = buscar.value.toLowerCase();
            const rolSeleccionado = filtroRol.value;
            const orden = filtroOrden.value;
            const filas = Array.from(document.querySelectorAll('#tablaUsuarios tr'));

            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase();
                const correo = fila.querySelector('.correo')?.textContent.toLowerCase();
                const rol = fila.querySelector('.role')?.classList[1];
                fila.style.display =
                    (!texto || nombre.includes(texto) || correo.includes(texto)) &&
                    (!rolSeleccionado || rol === rolSeleccionado)
                        ? ''
                        : 'none';
            });

            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const idA = parseInt(a.querySelector('td').textContent.trim());
                const idB = parseInt(b.querySelector('td').textContent.trim());
                if (orden === 'alfabetico')
                    return a.querySelector('.nombre').textContent.localeCompare(b.querySelector('.nombre').textContent);
                if (orden === 'inverso')
                    return b.querySelector('.nombre').textContent.localeCompare(a.querySelector('.nombre').textContent);
                if (orden === 'antiguos') return idA - idB;
                if (orden === 'recientes') return idB - idA;
                return 0;
            });

            const tbody = document.getElementById('tablaUsuarios');
            visibles.forEach(f => tbody.appendChild(f));
        }
    </script>



@endsection
