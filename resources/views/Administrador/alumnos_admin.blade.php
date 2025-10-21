@extends('layouts.app')
@section('title', 'Gestión de Alumnos | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_alumnos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_alum_modal.css') }}">
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
                <li class="menu-item active"><a href="{{ route('administrador.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a></li>
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
                <h2><i class="fa-solid fa-user-graduate"></i> Gestión de Alumnos</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>

            </header>

            <section class="alumnos-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de alumnos registrados</h3>
                    <div class="actions-right">
                        <input type="text" placeholder="Buscar alumno..." id="buscarAlumno">

                        <!-- 🔹 NUEVOS FILTROS -->
                        <select id="filtroNivel">
                            <option value="">Todos los niveles</option>
                            <option value="Principiantes I">Principiantes I</option>
                            <option value="Principiantes II">Principiantes II</option>
                            <option value="Avanzados I">Avanzados I</option>
                            <option value="Avanzados II">Avanzados II</option>
                        </select>

                        <select id="filtroGrado">
                            <option value="">Todos los grados</option>
                            <option value="Novatos">Novatos</option>
                            <option value="Expertos">Expertos</option>
                        </select>

                        <select id="filtroSucursal">
                            <option value="">Todas las sucursales</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->nombre }}">{{ $branch->nombre }}</option>
                            @endforeach
                        </select>

                        <select id="ordenAlumnos">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="alfabetico">A-Z</option>
                            <option value="inverso">Z-A</option>
                        </select>

                        <button class="btn-new" id="btnNuevoAlumno">
                            <i class="fa-solid fa-plus"></i> Nuevo alumno
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Fecha Nacimiento</th>
                        <th>Nivel</th>
                        <th>Grado</th>
                        <th>Sucursal</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaAlumnos">
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->id }}</td>
                            <td class="nombre">{{ $student->nombres }}</td>
                            <td>{{ $student->telefono ?? '—' }}</td>
                            <td>
                                {{ $student->fecha_nacimiento ? \Carbon\Carbon::parse($student->fecha_nacimiento)->format('Y-m-d') : '—' }}
                            </td>
                            <td>{{ $student->level }}</td>
                            <td>{{ $student->grade }}</td>
                            <td>{{ $student->branch->nombre ?? '—' }}</td>
                            <td class="acciones">
                                <button class="btn-edit"
                                        data-id="{{ $student->id }}"
                                        data-nombre="{{ $student->nombres }}"
                                        data-telefono="{{ $student->telefono }}"
                                        data-fecha="{{ $student->fecha_nacimiento }}"
                                        data-level="{{ $student->level }}"
                                        data-grade="{{ $student->grade }}"
                                        data-branch="{{ $student->branch_id }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-delete" data-id="{{ $student->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);">No hay alumnos registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVO ALUMNO === -->
    <div id="modalNuevoAlumno" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-user-plus"></i> Nuevo Alumno</h3>
            <form method="POST" action="{{ route('administrador.alumnos.store') }}" class="form-modal">
                @csrf

                <div class="campo-full">
                    <label>Usuario asociado (rol estudiante)</label>
                    <select name="user_id" required>
                        <option value="">Selecciona un usuario...</option>
                        @foreach($usuariosNoRegistrados as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-full">
                    <label>Nombre completo</label>
                    <input type="text" name="nombres" required>
                </div>

                <div>
                    <label>Teléfono</label>
                    <input type="text"
                           name="telefono"
                            inputmode="numeric"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            title="Debe contener exactamente 8 dígitos numéricos.">
                </div>
                <div>
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento">
                </div>

                <div>
                    <label>Nivel</label>
                    <select name="level" required>
                        <option value="Principiantes I">Principiantes I</option>
                        <option value="Principiantes II">Principiantes II</option>
                        <option value="Avanzados I">Avanzados I</option>
                        <option value="Avanzados II">Avanzados II</option>
                    </select>
                </div>
                <div>
                    <label>Grado</label>
                    <select name="grade" required>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
                </div>

                <div class="campo-full">
                    <label>Sucursal</label>
                    <select name="branch_id" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevoAlumno')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR ALUMNO === -->
    <div id="modalEditarAlumno" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen"></i> Editar Alumno</h3>
            <form method="POST" id="formEditarAlumno" class="form-modal">
                @csrf
                @method('PUT')

                <div class="campo-full">
                    <label>Nombre completo</label>
                    <input type="text" name="nombres" id="editNombre" required>
                </div>

                <div>
                    <label>Teléfono</label>
                    <input type="text"
                           name="telefono"
                           id="editTelefono"
                            inputmode="numeric"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            title="Debe contener exactamente 8 dígitos numéricos.">

                </div>
                <div>
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="editFecha">
                </div>

                <div>
                    <label>Nivel</label>
                    <select name="level" id="editLevel" required>
                        <option value="Principiantes I">Principiantes I</option>
                        <option value="Principiantes II">Principiantes II</option>
                        <option value="Avanzados I">Avanzados I</option>
                        <option value="Avanzados II">Avanzados II</option>
                    </select>
                </div>
                <div>
                    <label>Grado</label>
                    <select name="grade" id="editGrade" required>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
                </div>

                <div class="campo-full">
                    <label>Sucursal</label>
                    <select name="branch_id" id="editBranch" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarAlumno')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR ALUMNO === -->
    <div id="modalEliminarAlumno" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este alumno? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarAlumno">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarAlumno')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === NUEVO ALUMNO ===
        const modalNuevo = document.getElementById('modalNuevoAlumno');
        const btnNuevo = document.getElementById('btnNuevoAlumno');
        btnNuevo.addEventListener('click', () => modalNuevo.classList.add('show'));

        // === ALERTA FLOTANTE ===
        function showFloatingAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
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
        const phoneRegex = /^[0-9]{8}$/;

        // === RESTRICCIÓN FECHA ===
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const maxDate = `${yyyy}-${mm}-${dd}`;

        document.querySelectorAll('input[name="fecha_nacimiento"], #editFecha').forEach(input => {
            input.max = maxDate;
        });

        function isValidAge(dateStr) {
            const birthDate = new Date(dateStr);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age >= 16;
        }

        // === FORM NUEVO ALUMNO ===
        const formNuevo = document.querySelector('#modalNuevoAlumno form');
        formNuevo.setAttribute('novalidate', true);

        formNuevo.addEventListener('submit', e => {
            e.preventDefault(); // ✅ evita que se envíe si hay errores
            const form = e.target;
            const nombre = form.nombres.value.trim();
            const telefono = form.telefono.value.trim();
            const fecha = form.fecha_nacimiento.value;

            if (!nameRegex.test(nombre))
                return showFloatingAlert('❌ El nombre solo puede contener letras y espacios.');
            if (telefono && !phoneRegex.test(telefono))
                return showFloatingAlert('❌ El teléfono debe tener exactamente 8 dígitos numéricos.');
            if (fecha) {
                if (new Date(fecha) > today)
                    return showFloatingAlert('❌ La fecha de nacimiento no puede ser futura.');
                if (!isValidAge(fecha))
                    return showFloatingAlert('❌ El alumno debe tener al menos 16 años.');
            }

            // ✅ Si todo está correcto
            form.submit();
        });

        // === FORM EDITAR ALUMNO ===
        const modalEditar = document.getElementById('modalEditarAlumno');
        const formEditar = document.getElementById('formEditarAlumno');
        formEditar.setAttribute('novalidate', true);

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.action = `/administrador/alumnos/${id}`;
                document.getElementById('editNombre').value = btn.dataset.nombre;
                document.getElementById('editTelefono').value = btn.dataset.telefono;
                document.getElementById('editFecha').value = btn.dataset.fecha;
                document.getElementById('editLevel').value = btn.dataset.level;
                document.getElementById('editGrade').value = btn.dataset.grade;
                document.getElementById('editBranch').value = btn.dataset.branch;
                modalEditar.classList.add('show');
            });
        });

        formEditar.addEventListener('submit', e => {
            e.preventDefault(); // ✅ evita guardar sin pasar validaciones
            const form = e.target;
            const nombre = form.nombres.value.trim();
            const telefono = form.telefono.value.trim();
            const fecha = form.fecha_nacimiento.value;

            if (!nameRegex.test(nombre))
                return showFloatingAlert('❌ El nombre solo puede contener letras y espacios.');
            if (telefono && !phoneRegex.test(telefono))
                return showFloatingAlert('❌ El teléfono debe tener exactamente 8 dígitos numéricos.');
            if (fecha) {
                if (new Date(fecha) > today)
                    return showFloatingAlert('❌ La fecha de nacimiento no puede ser futura.');
                if (!isValidAge(fecha))
                    return showFloatingAlert('❌ El alumno debe tener al menos 16 años.');
            }

            // ✅ Si todo está correcto
            form.submit();
        });

        // === ELIMINAR ===
        const modalEliminar = document.getElementById('modalEliminarAlumno');
        const formEliminar = document.getElementById('formEliminarAlumno');
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = `/administrador/alumnos/${id}`;
                modalEliminar.classList.add('show');
            });
        });

        // === BUSCAR Y FILTROS ===
        const buscar = document.getElementById('buscarAlumno');
        const orden = document.getElementById('ordenAlumnos');
        const filtroNivel = document.getElementById('filtroNivel');
        const filtroGrado = document.getElementById('filtroGrado');
        const filtroSucursal = document.getElementById('filtroSucursal');

        [buscar, orden, filtroNivel, filtroGrado, filtroSucursal].forEach(el =>
            el.addEventListener('input', filtrarYOrdenar)
        );

        function filtrarYOrdenar() {
            const texto = buscar.value.toLowerCase();
            const nivel = filtroNivel.value;
            const grado = filtroGrado.value;
            const sucursal = filtroSucursal.value;
            const ordenSeleccionado = orden.value;
            const filas = Array.from(document.querySelectorAll('#tablaAlumnos tr'));

            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase();
                const nivelF = fila.children[4].textContent;
                const gradoF = fila.children[5].textContent;
                const sucursalF = fila.children[6].textContent;

                const visible =
                    (!texto || nombre.includes(texto)) &&
                    (!nivel || nivelF === nivel) &&
                    (!grado || gradoF === grado) &&
                    (!sucursal || sucursalF === sucursal);

                fila.style.display = visible ? '' : 'none';
            });

            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const nA = a.querySelector('.nombre').textContent.toLowerCase();
                const nB = b.querySelector('.nombre').textContent.toLowerCase();
                const idA = parseInt(a.querySelector('td').textContent);
                const idB = parseInt(b.querySelector('td').textContent);
                if (ordenSeleccionado === 'alfabetico') return nA.localeCompare(nB);
                if (ordenSeleccionado === 'inverso') return nB.localeCompare(nA);
                if (ordenSeleccionado === 'antiguos') return idA - idB;
                if (ordenSeleccionado === 'recientes') return idB - idA;
                return 0;
            });

            const tbody = document.getElementById('tablaAlumnos');
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
