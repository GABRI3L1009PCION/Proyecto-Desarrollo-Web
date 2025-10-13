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
                <p>Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <section class="users-section">
                <div class="header-actions">
                    <div class="left-group">
                        <h3><i class="fa-solid fa-users"></i> Lista de usuarios registrados</h3>

                        <!-- 🔍 BUSCADOR -->
                        <input type="text" id="buscarUsuario" class="filtro-input" placeholder="Buscar por nombre o correo...">

                        <!-- 🎚️ FILTRO ROL -->
                        <select id="filtroRol" class="filtro-select">
                            <option value="">Todos los roles</option>
                            <option value="admin">Administrador</option>
                            <option value="catedratico">Catedrático</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="secretaria">Secretaria</option>
                        </select>

                        <!-- 🔄 ORDEN -->
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

    <!-- === MODALES (IGUALES A TU VERSIÓN ORIGINAL) === -->
    <div id="modalNuevoUsuario" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-plus"></i> Nuevo Usuario</h3>
            <form method="POST" action="{{ route('administrador.usuarios.store') }}" class="form-modal">
                @csrf
                <label>Nombre completo</label>
                <input type="text" name="name" required>
                <label>Correo electrónico</label>
                <input type="email" name="email" required>
                <label>Contraseña</label>
                <input type="password" name="password" required>
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

    <div id="modalEditarUsuario" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-pen"></i> Editar Usuario</h3>
            <form method="POST" id="formEditarUsuario" class="form-modal">
                @csrf
                @method('PUT')
                <label>Nombre completo</label>
                <input type="text" name="name" id="editName" required>
                <label>Correo electrónico</label>
                <input type="email" name="email" id="editEmail" required>
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

        document.querySelector('#modalNuevoUsuario form').addEventListener('submit', async e => {
            e.preventDefault();
            const form = e.target;
            const data = {
                name: form.name.value.trim(),
                email: form.email.value.trim(),
                password: form.password.value.trim(),
                role: form.role.value
            };
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
                    const msg = document.createElement('div');
                    msg.className = 'alert success';
                    msg.textContent = '✅ Usuario creado correctamente.';
                    document.body.appendChild(msg);
                    setTimeout(() => msg.remove(), 3500);
                    cerrarModal('modalNuevoUsuario');
                    setTimeout(() => window.location.reload(), 700);
                } else {
                    const err = await res.json();
                    alert(err.message || '❌ Error al crear el usuario.');
                }
            } catch {
                alert('⚠️ Error de conexión con el servidor.');
            }
        });

        // === EDITAR Y ELIMINAR USUARIO ===
        const modalEditar = document.getElementById('modalEditarUsuario');
        const formEditar = document.getElementById('formEditarUsuario');
        document.querySelectorAll('.edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.action = `/administrador/usuarios/${id}`;
                document.getElementById('editName').value = btn.dataset.name;
                document.getElementById('editEmail').value = btn.dataset.email;
                document.getElementById('editRole').value = btn.dataset.role;
                modalEditar.classList.add('show');
            });
        });

        const modalEliminar = document.getElementById('modalEliminarUsuario');
        const formEliminar = document.getElementById('formEliminarUsuario');
        document.querySelectorAll('.delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = `/administrador/usuarios/${id}`;
                modalEliminar.classList.add('show');
            });
        });

        // === CERRAR ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape') [modalNuevo, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });
        function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); }));

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

            // Filtrar
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

            // Ordenar visibles (por ID para recientes/antiguos)
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
                if (orden === 'antiguos') return idA - idB;
                if (orden === 'recientes') return idB - idA;

                return 0;
            });

            const tbody = document.getElementById('tablaUsuarios');
            visibles.forEach(f => tbody.appendChild(f));
        }
    </script>
@endsection
