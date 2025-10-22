@extends('layouts.app')
@section('title', 'Gestión de Inscripciones | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/secretaria_alumnos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/sec_inscripciones_modal.css') }}">
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
                <li class="menu-item"><a href="{{ route('secretaria.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a></li>
                <li class="menu-item"><a href="{{ route('secretaria.alumnos') }}"><i class="fa-solid fa-user-graduate"></i> <span>Alumnos</span></a></li>
                <li class="menu-item active"><a href="{{ route('secretaria.inscripciones') }}"><i class="fa-solid fa-clipboard-list"></i> <span>Inscripciones</span></a></li>
                <li class="menu-item"><a href="{{ route('secretaria.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-clipboard-list"></i> Gestión de Inscripciones</h2>
                <p class="welcome">Bienvenida, <strong>{{ Auth::user()->name ?? 'Secretaria' }}</strong></p>
            </header>

            <!-- === LISTADO DE INSCRIPCIONES === -->
            <section class="inscripciones-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de inscripciones</h3>
                    <div class="actions-right">
                        <input type="text" id="buscarInscripcion" placeholder="Buscar alumno...">

                        <select id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="activa">Activa</option>
                            <option value="retirada">Retirada</option>
                            <option value="finalizada">Finalizada</option>
                        </select>

                        <select id="filtroSucursal">
                            <option value="">Todas las sucursales</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->nombre }}">{{ $branch->nombre }}</option>
                            @endforeach
                        </select>

                        <select id="ordenInscripciones">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguas">Más antiguas</option>
                        </select>

                        <button class="btn-new" id="btnNuevaInscripcion">
                            <i class="fa-solid fa-plus"></i> Nueva inscripción
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Curso</th>
                        <th>Catedrático</th>
                        <th>Sucursal</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaInscripciones">
                    @forelse($inscripciones as $inscripcion)
                        <tr>
                            <td>{{ $inscripcion->id }}</td>
                            <td>{{ $inscripcion->student->nombres }}</td>
                            <td>{{ $inscripcion->offering->course->nombre }}</td>
                            <td>{{ $inscripcion->offering->teacher->nombres ?? '—' }}</td>
                            <td>{{ $inscripcion->offering->branch->nombre ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($inscripcion->fecha)->format('Y-m-d') }}</td>
                            <td>
                                <span class="estado {{ $inscripcion->status }}">
                                    {{ ucfirst($inscripcion->status) }}
                                </span>
                            </td>
                            <td class="acciones">
                                <button class="btn-edit"
                                        data-id="{{ $inscripcion->id }}"
                                        data-status="{{ $inscripcion->status }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn-delete" data-id="{{ $inscripcion->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:var(--muted);">No hay inscripciones registradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>

            <input type="hidden" id="inscripcionesData" value='@json($inscripcionesData)'>

        </main>
    </div>

    <!-- === MODAL NUEVA INSCRIPCIÓN === -->
    <div id="modalNuevaInscripcion" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-plus"></i> Nueva Inscripción</h3>
            <form method="POST" action="{{ route('secretaria.inscripciones.store') }}" class="form-modal">
                @csrf
                <div class="custom-select-wrapper">
                    <label>Alumno</label>
                    <div class="custom-select" id="selectAlumnoCustom">
                        <div class="selected-option">Selecciona un alumno...</div>

                        <div class="options-list">
                            <input type="text" id="filterAlumnos" placeholder="🔍 Buscar alumno..." autocomplete="off">
                            <div class="options-container">
                                @foreach($students as $student)
                                    <div class="option" data-value="{{ $student->id }}">
                                        <div class="opt-main">👤 {{ $student->nombres }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="student_id" id="studentHidden" required>
                </div>


                <div class="custom-select-wrapper">
                    <label>Oferta (Curso - Docente - Sucursal)</label>
                    <div class="custom-select" id="selectOfertaCustom">
                        <div class="selected-option">Selecciona una oferta...</div>

                        <div class="options-list">
                            <input type="text" id="filterOfertas" placeholder="🔍 Buscar curso..." autocomplete="off">
                            <div class="options-container">
                                @foreach($offerings as $offer)
                                    <div class="option" data-value="{{ $offer->id }}">
                                        <div class="opt-main">{{ $offer->course->nombre }}</div>
                                        <div class="opt-sub">
                                            <div>👨‍🏫 <span>{{ $offer->teacher->nombres ?? 'Sin docente' }}</span></div>
                                            <div>🏫 <span>{{ $offer->branch->nombre }}</span></div>
                                            <div class="opt-cupo">🎟️ Cupo: <strong>{{ $offer->cupo }}</strong></div>
                                        </div>
                                    </div>

                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="offering_id" id="offeringHidden" required>
                </div>



                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevaInscripcion')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR ESTADO === -->
    <div id="modalEditarInscripcion" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen"></i> Cambiar Estado</h3>
            <form method="POST" id="formEditarInscripcion" class="form-modal">
                @csrf
                @method('PUT')
                <div>
                    <label>Estado</label>
                    <select name="status" id="editStatus" required>
                        <option value="activa">Activa</option>
                        <option value="retirada">Retirada</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarInscripcion')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR === -->
    <div id="modalEliminarInscripcion" class="modal-overlay">
        <div class="modal-content modal-delete">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Seguro que deseas eliminar esta inscripción? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarInscripcion">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarInscripcion')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === NUEVA INSCRIPCIÓN ===
        const modalNueva = document.getElementById('modalNuevaInscripcion');
        const modalEditar = document.getElementById('modalEditarInscripcion');
        const modalEliminar = document.getElementById('modalEliminarInscripcion');
        const btnNueva = document.getElementById('btnNuevaInscripcion');

        if (btnNueva) {
            btnNueva.addEventListener('click', () => {
                // Cierra otros modales antes de abrir uno nuevo
                [modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
                modalNueva.classList.add('show');
            });
        }

        // === ALERTAS FLOTANTES ===
        function showFloatingAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.textContent = message;
            Object.assign(alert.style, {
                position: 'fixed',
                top: '15px',
                left: '50%',
                transform: 'translateX(-50%)',
                background:
                    type === 'error' ? '#e74c3c' :
                        type === 'success' ? '#2ecc71' :
                            '#f1c40f',
                color: '#fff',
                padding: '10px 20px',
                borderRadius: '8px',
                boxShadow: '0 4px 10px rgba(0,0,0,0.3)',
                fontSize: '0.9rem',
                zIndex: '9999',
                opacity: '0',
                transition: 'opacity 0.3s ease'
            });
            document.body.appendChild(alert);
            setTimeout(() => alert.style.opacity = '1', 100);
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 400);
            }, 3500);
        }

        // === EDITAR ===
        const formEditar = document.getElementById('formEditarInscripcion');
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEditar.action = `/secretaria/inscripciones/${id}`;
                document.getElementById('editStatus').value = btn.dataset.status;

                [modalNueva, modalEliminar].forEach(m => m.classList.remove('show'));
                modalEditar.classList.add('show');
            });
        });

        // === ELIMINAR ===
        const formEliminar = document.getElementById('formEliminarInscripcion');
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                formEliminar.action = `/secretaria/inscripciones/${id}`;
                [modalNueva, modalEditar].forEach(m => m.classList.remove('show'));
                modalEliminar.classList.add('show');
            });
        });

        // === FILTROS Y ORDEN ===
        const buscar = document.getElementById('buscarInscripcion');
        const estado = document.getElementById('filtroEstado');
        const sucursal = document.getElementById('filtroSucursal');
        const orden = document.getElementById('ordenInscripciones');

        [buscar, estado, sucursal, orden].forEach(el =>
            el?.addEventListener('input', filtrarYOrdenar)
        );

        function filtrarYOrdenar() {
            const texto = buscar.value.toLowerCase();
            const estadoF = estado.value;
            const sucursalF = sucursal.value;
            const ordenSeleccionado = orden.value;
            const filas = Array.from(document.querySelectorAll('#tablaInscripciones tr'));

            filas.forEach(fila => {
                const alumno = fila.children[1].textContent.toLowerCase();
                const sucursalT = fila.children[4].textContent;
                const estadoT = fila.children[6].textContent.toLowerCase();

                const visible =
                    (!texto || alumno.includes(texto)) &&
                    (!estadoF || estadoT === estadoF) &&
                    (!sucursalF || sucursalT === sucursalF);

                fila.style.display = visible ? '' : 'none';
            });

            const visibles = filas.filter(f => f.style.display !== 'none');
            visibles.sort((a, b) => {
                const idA = parseInt(a.children[0].textContent);
                const idB = parseInt(b.children[0].textContent);
                if (ordenSeleccionado === 'antiguas') return idA - idB;
                if (ordenSeleccionado === 'recientes') return idB - idA;
                return 0;
            });

            const tbody = document.getElementById('tablaInscripciones');
            visibles.forEach(f => tbody.appendChild(f));
        }

        // === CERRAR MODALES ===
        window.addEventListener('keydown', e => {
            if (e.key === 'Escape')
                [modalNueva, modalEditar, modalEliminar].forEach(m => m.classList.remove('show'));
        });

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        document.querySelectorAll('.modal-overlay').forEach(o =>
            o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); })
        );

        // === FILTRAR OFERTAS SEGÚN ALUMNO SELECCIONADO ===
        const inscripcionesExistentes = JSON.parse(document.getElementById('inscripcionesData').value || '[]');
        const selectAlumno = document.querySelector('select[name="student_id"]');
        const selectOferta = document.querySelector('select[name="offering_id"]');

        if (selectAlumno && selectOferta) {
            selectAlumno.addEventListener('change', () => {
                const alumnoId = selectAlumno.value;
                Array.from(selectOferta.options).forEach(opt => {
                    opt.style.display = '';
                    opt.disabled = false;
                    if (opt.textContent.includes('Ya inscrito')) {
                        opt.textContent = opt.textContent.replace(' (Ya inscrito)', '');
                    }
                });

                if (!alumnoId) return;

                const inscritas = inscripcionesExistentes
                    .filter(i => i.student_id == alumnoId)
                    .map(i => i.offering_id);

                Array.from(selectOferta.options).forEach(opt => {
                    if (inscritas.includes(parseInt(opt.value))) {
                        opt.disabled = true;
                        opt.textContent += ' (Ya inscrito)';
                    }
                });
                selectOferta.value = '';
            });
        }

        // === MENSAJES DE ÉXITO DESDE LARAVEL ===
        @if (session('success'))
        showFloatingAlert("✅ {{ session('success') }}", 'success');
        @endif
        @if (session('updated'))
        showFloatingAlert("✏️ {{ session('updated') }}", 'success');
        @endif
        @if (session('deleted'))
        showFloatingAlert("🗑️ {{ session('deleted') }}", 'success');
        @endif

        // === CUSTOM SELECT CON BUSCADOR EN TIEMPO REAL ===
        const customSelect = document.getElementById('selectOfertaCustom');
        if (customSelect) {
            const selected = customSelect.querySelector('.selected-option');
            const optionsList = customSelect.querySelector('.options-list');
            const optionsContainer = customSelect.querySelector('.options-container');
            const hiddenInput = document.getElementById('offeringHidden');
            const filterInput = document.getElementById('filterOfertas');

            function normalizeText(str) {
                return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            }

            // Abrir / Cerrar menú
            selected.addEventListener('click', () => {
                customSelect.classList.toggle('open');
                filterInput.value = '';
                filterOptions('');
                if (customSelect.classList.contains('open')) {
                    setTimeout(() => filterInput.focus(), 150);
                }
            });

            // Seleccionar opción
            optionsContainer.querySelectorAll('.option').forEach(opt => {
                opt.addEventListener('click', () => {
                    selected.textContent = opt.textContent.trim();
                    hiddenInput.value = opt.dataset.value;
                    customSelect.classList.remove('open');
                });
            });

            // Filtrado en tiempo real
            filterInput.addEventListener('input', e => {
                const searchTerm = normalizeText(e.target.value);
                filterOptions(searchTerm);
            });

            function filterOptions(searchTerm) {
                optionsContainer.querySelectorAll('.option').forEach(opt => {
                    const text = normalizeText(opt.textContent);
                    opt.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            }

            // Cerrar al hacer clic fuera
            window.addEventListener('click', e => {
                if (!customSelect.contains(e.target)) customSelect.classList.remove('open');
            });
        }
        // === CUSTOM SELECT PARA ALUMNOS ===
        const selectAlumnoCustom = document.getElementById('selectAlumnoCustom');
        const selectedAlumno = selectAlumnoCustom.querySelector('.selected-option');
        const optionsListAlumno = selectAlumnoCustom.querySelector('.options-list');
        const optionsContainerAlumno = selectAlumnoCustom.querySelector('.options-container');
        const hiddenAlumno = document.getElementById('studentHidden');
        const filterAlumno = document.getElementById('filterAlumnos');

        // Normalizar texto
        function normalizeText(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        // Abrir/Cerrar
        selectedAlumno.addEventListener('click', () => {
            selectAlumnoCustom.classList.toggle('open');
            filterAlumno.value = '';
            filterOptionsAlumno('');
            if (selectAlumnoCustom.classList.contains('open')) {
                setTimeout(() => filterAlumno.focus(), 150);
            }
        });

        // Seleccionar
        optionsContainerAlumno.querySelectorAll('.option').forEach(opt => {
            opt.addEventListener('click', () => {
                selectedAlumno.textContent = opt.textContent.trim();
                hiddenAlumno.value = opt.dataset.value;
                selectAlumnoCustom.classList.remove('open');
            });
        });

        // Filtrar en tiempo real
        filterAlumno.addEventListener('input', e => {
            const searchTerm = normalizeText(e.target.value);
            filterOptionsAlumno(searchTerm);
        });

        function filterOptionsAlumno(searchTerm) {
            optionsContainerAlumno.querySelectorAll('.option').forEach(opt => {
                const text = normalizeText(opt.textContent);
                opt.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        }

        // Cerrar si se hace clic fuera
        window.addEventListener('click', e => {
            if (!selectAlumnoCustom.contains(e.target)) selectAlumnoCustom.classList.remove('open');
        });

    </script>
@endsection
