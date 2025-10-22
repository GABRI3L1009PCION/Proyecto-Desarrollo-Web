@extends('layouts.app')
@section('title', 'Gestión de Catedráticos | Secretaría | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/sec_catedraticos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_cate_modal.css') }}">
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
                <li class="menu-item"><a href="{{ route('secretaria.inscripciones') }}"><i class="fa-solid fa-file-pen"></i> <span>Inscripciones</span></a></li>
                <li class="menu-item active"><a href="{{ route('secretaria.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-chalkboard-user"></i> Gestión de Catedráticos</h2>
                <p class="welcome">Bienvenida, <strong>{{ Auth::user()->name ?? 'Secretaria' }}</strong></p>
            </header>

            <section class="catedraticos-section">
                <div class="header-actions">
                    <h3><i class="fa-solid fa-list"></i> Lista de catedráticos registrados</h3>
                    <div class="actions-right">
                        <input type="text" placeholder="Buscar catedrático..." id="buscarCatedratico">
                        <select id="ordenCatedraticos">
                            <option value="recientes">Más recientes</option>
                            <option value="antiguos">Más antiguos</option>
                            <option value="alfabetico">A-Z</option>
                            <option value="inverso">Z-A</option>
                        </select>
                        <button class="btn-new" id="btnNuevoCatedratico">
                            <i class="fa-solid fa-plus"></i> Nuevo catedrático
                        </button>
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Sucursal</th>
                        <th>Teléfono</th>
                        <th>Cursos asignados</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaCatedraticos">
                    @forelse($teachers as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td class="usuario">{{ $t->user->name ?? 'Sin usuario' }}</td>
                            <td class="nombre">{{ $t->nombres }}</td>
                            <td>{{ $t->branch->nombre ?? '—' }}</td>
                            <td>{{ $t->telefono ?? '—' }}</td>

                            @php $count = $t->offerings->count(); @endphp
                            <td class="cursos">
                                <button class="btn-ver-cursos"
                                        data-id="{{ $t->id }}"
                                        data-nombre="{{ $t->nombres }}"
                                        data-cursos='@json($t->offerings)'>
                                    @if($count === 0)
                                        <span class="badge badge-gray">Sin asignaciones</span>
                                    @elseif($count === 1)
                                        <span class="badge badge-blue">{{ $t->offerings->first()->course->nombre }}</span>
                                    @else
                                        <span class="badge badge-gold">{{ $count }} cursos asignados</span>
                                    @endif
                                </button>
                            </td>

                            <td class="acciones">
                                <button class="btn-edit"
                                        data-id="{{ $t->id }}"
                                        data-nombres="{{ $t->nombres }}"
                                        data-telefono="{{ $t->telefono }}"
                                        data-branch="{{ $t->branch_id }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @if($count == 0)
                                    <button class="btn-delete" data-id="{{ $t->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--muted);">No hay catedráticos registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- === MODAL NUEVO CATEDRÁTICO === -->
    <div id="modalNuevoCatedratico" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-plus"></i> Nuevo Catedrático</h3>
            <form method="POST" action="{{ route('secretaria.catedraticos.store') }}" class="form-modal">
                @csrf
                <!-- === SELECT PERSONALIZADO: USUARIO === -->
                <div class="custom-select-wrapper">
                    <label>Usuario</label>
                    <div class="custom-select" id="selectUsuarioCustom">
                        <div class="selected-option">Seleccione un usuario...</div>

                        <div class="options-list">
                            <input type="text" id="filterUsuarios" placeholder="🔍 Buscar usuario..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\User::where('role','catedratico')->whereDoesntHave('teacher')->get() as $u)
                                    <div class="option" data-value="{{ $u->id }}">
                                        <div class="opt-main">{{ $u->name }}</div>
                                        <div class="opt-sub">
                                            <span>📧 {{ $u->email }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" id="usuarioHidden" required>
                </div>

                <!-- === SELECT PERSONALIZADO: SUCURSAL === -->
                <div class="custom-select-wrapper">
                    <label>Sucursal</label>
                    <div class="custom-select" id="selectSucursalCustom">
                        <div class="selected-option">Seleccione una sucursal...</div>

                        <div class="options-list">
                            <input type="text" id="filterSucursales" placeholder="🔍 Buscar sucursal..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\Branch::all() as $b)
                                    <div class="option" data-value="{{ $b->id }}">
                                        <div class="opt-main">🏫 {{ $b->nombre }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="branch_id" id="sucursalHidden" required>
                </div>

                <div>
                    <label>Nombre completo</label>
                    <input type="text" name="nombres" required>
                </div>
                <div>
                    <label>Teléfono</label>
                    <input type="text" name="telefono">
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalNuevoCatedratico')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL EDITAR CATEDRÁTICO === -->
    <div id="modalEditarCatedratico" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-pen"></i> Editar Catedrático</h3>
            <form method="POST" id="formEditarCatedratico" class="form-modal">
                @csrf
                @method('PUT')

                <div>
                    <label>Nombre completo</label>
                    <input type="text" name="nombres" id="editNombres" required>
                </div>

                <div>
                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="editTelefono">
                </div>

                <!-- === SELECT PERSONALIZADO: SUCURSAL (EDITAR) === -->
                <div class="custom-select-wrapper">
                    <label>Sucursal</label>
                    <div class="custom-select" id="selectSucursalEditCustom">
                        <div class="selected-option" id="selectedSucursalEdit">Seleccione una sucursal...</div>

                        <div class="options-list">
                            <input type="text" id="filterSucursalesEdit" placeholder="🔍 Buscar sucursal..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\Branch::all() as $b)
                                    <div class="option" data-value="{{ $b->id }}">
                                        <div class="opt-main">🏫 {{ $b->nombre }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="branch_id" id="branchHiddenEdit" required>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-confirm">Actualizar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarCatedratico')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>


    <!-- === MODAL ELIMINAR === -->
    <div id="modalEliminarCatedratico" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás segura de que deseas eliminar este catedrático? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarCatedratico">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarCatedratico')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL VER CURSOS ASIGNADOS === -->
    <div id="modalCursos" class="modal-overlay">
        <div class="modal-content modal-cursos">
            <h3><i class="fa-solid fa-book"></i> Cursos asignados a <span id="nombreCatedratico"></span></h3>
            <div id="listaCursos" class="tabla-cursos">
                <table class="data-table cursos-table">
                    <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Grado</th>
                        <th>Nivel</th>
                        <th>Sucursal</th>
                        <th>Año</th>
                        <th>Ciclo</th>
                        <th>Cupo</th>
                        <th>Horario</th>
                    </tr>
                    </thead>
                    <tbody id="tablaCursosAsignados">
                    <tr><td colspan="8" style="text-align:center;color:var(--muted);">Selecciona un catedrático para ver sus cursos.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalCursos')">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/secretaria_catedraticos.js') }}"></script>

    <script>
        @if(session('success'))
        showFloatingAlert("✅ {{ session('success') }}", 'success');
        @endif

        @if(session('updated'))
        showFloatingAlert("✏️ {{ session('updated') }}", 'success');
        @endif

        @if(session('deleted'))
        showFloatingAlert("🗑️ {{ session('deleted') }}", 'success');
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // === FUNCIÓN REUTILIZABLE PARA SELECT PERSONALIZADO ===
            function setupCustomSelect(selectId, hiddenId, filterId) {
                const select = document.getElementById(selectId);
                if (!select) return;

                const selected = select.querySelector('.selected-option');
                const optionsList = select.querySelector('.options-list');
                const container = select.querySelector('.options-container');
                const hidden = document.getElementById(hiddenId);
                const filter = document.getElementById(filterId);

                const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

                selected.addEventListener('click', () => {
                    select.classList.toggle('open');
                    filter.value = '';
                    filterOptions('');
                    if (select.classList.contains('open')) {
                        setTimeout(() => filter.focus(), 150);
                    }
                });

                container.querySelectorAll('.option').forEach(opt => {
                    opt.addEventListener('click', () => {
                        selected.textContent = opt.querySelector('.opt-main').textContent.trim();
                        hidden.value = opt.dataset.value;
                        select.classList.remove('open');
                    });
                });

                filter.addEventListener('input', e => {
                    const term = normalize(e.target.value);
                    filterOptions(term);
                });

                function filterOptions(term) {
                    container.querySelectorAll('.option').forEach(opt => {
                        const text = normalize(opt.textContent);
                        opt.style.display = text.includes(term) ? 'block' : 'none';
                    });
                }

                window.addEventListener('click', e => {
                    if (!select.contains(e.target)) select.classList.remove('open');
                });
            }

            // Activar select personalizado de editar
            setupCustomSelect('selectSucursalEditCustom', 'branchHiddenEdit', 'filterSucursalesEdit');

            // === CARGAR SUCURSAL ACTUAL EN EDITAR ===
            const formEditar = document.getElementById('formEditarCatedratico');
            const modalEditar = document.getElementById('modalEditarCatedratico');

            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    const nombres = btn.dataset.nombres;
                    const telefono = btn.dataset.telefono;
                    const branchId = btn.dataset.branch;

                    formEditar.action = `/secretaria/catedraticos/${id}`;
                    document.getElementById('editNombres').value = nombres;
                    document.getElementById('editTelefono').value = telefono;

                    // Actualizar el select de sucursal
                    const selected = document.getElementById('selectedSucursalEdit');
                    const hidden = document.getElementById('branchHiddenEdit');
                    const options = document.querySelectorAll('#selectSucursalEditCustom .option');

                    selected.textContent = 'Seleccione una sucursal...';
                    hidden.value = '';

                    options.forEach(opt => {
                        if (opt.dataset.value === branchId) {
                            selected.textContent = opt.querySelector('.opt-main').textContent.trim();
                            hidden.value = branchId;
                        }
                    });

                    modalEditar.classList.add('show');
                });
            });
        });
    </script>

@endsection
