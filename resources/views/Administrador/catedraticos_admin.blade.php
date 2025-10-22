@extends('layouts.app')
@section('title', 'Gestión de Catedráticos | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_catedraticos.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/admin_cate_modal.css') }}">
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
                <li class="menu-item active"><a href="{{ route('administrador.catedraticos') }}"><i class="fa-solid fa-chalkboard-user"></i> <span>Catedráticos</span></a></li>
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
                <h2><i class="fa-solid fa-chalkboard-user"></i> Gestión de Catedráticos</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>

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
                        <th>Sucursal</th>
                        <th>Nombre</th>
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
                            <td>{{ $t->branch->nombre ?? '—' }}</td>
                            <td class="nombre">{{ $t->nombres }}</td>
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
                                <button class="btn-delete" data-id="{{ $t->id }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <button class="btn-assign" data-id="{{ $t->id }}">
                                    <i class="fa-solid fa-chalkboard"></i>
                                </button>
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
            <form method="POST" action="{{ route('administrador.catedraticos.store') }}" class="form-modal">
                @csrf
                <div class="custom-select-wrapper campo-full">
                    <label>Usuario asociado (rol catedrático)</label>
                    <div class="custom-select" id="selectUsuarioCustom">
                        <div class="selected-option">Selecciona un usuario...</div>

                        <div class="options-list">
                            <input type="text" id="filterUsuarios" placeholder="🔍 Buscar usuario..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\User::where('role','catedratico')->whereDoesntHave('teacher')->get() as $u)
                                    <div class="option" data-value="{{ $u->id }}">
                                        <div class="opt-main">👤 {{ $u->name }}</div>
                                        <div class="opt-sub">📧 {{ $u->email }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="user_id" id="userHidden" required>
                </div>

                <div class="custom-select-wrapper campo-full">
                    <label>Sucursal</label>
                    <div class="custom-select" id="selectSucursalCustom">
                        <div class="selected-option">Selecciona una sucursal...</div>

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
                    <input type="hidden" name="branch_id" id="branchHidden" required>
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
                <div class="custom-select-wrapper campo-full">
                    <label>Sucursal</label>
                    <div class="custom-select" id="selectSucursalEdit">
                        <div class="selected-option">Selecciona una sucursal...</div>

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

    <!-- === MODAL ASIGNAR CURSO === -->
    <div id="modalAsignarCurso" class="modal-overlay">
        <div class="modal-content">
            <h3><i class="fa-solid fa-chalkboard"></i> Asignar Curso al Catedrático</h3>

            <form method="POST" id="formAsignarCurso" class="form-modal form-grid-2">
                @csrf

                <!-- === CURSO === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Curso</label>
                    <div class="custom-select" id="selectCursoCustom">
                        <div class="selected-option">Seleccione un curso...</div>

                        <div class="options-list">
                            <input type="text" id="filterCursos" placeholder="🔍 Buscar curso..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\Course::all() as $c)
                                    <div class="option" data-value="{{ $c->id }}">
                                        <div class="opt-main">📘 {{ $c->nombre }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="course_id" id="courseHidden" required>
                </div>

                <!-- === SUCURSAL === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Sucursal</label>
                    <div class="custom-select" id="selectSucursalAsignar">
                        <div class="selected-option">Seleccione una sucursal...</div>

                        <div class="options-list">
                            <input type="text" id="filterSucursalAsignar" placeholder="🔍 Buscar sucursal..." autocomplete="off">
                            <div class="options-container">
                                @foreach(App\Models\Branch::all() as $b)
                                    <div class="option" data-value="{{ $b->id }}">
                                        <div class="opt-main">🏫 {{ $b->nombre }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="branch_id" id="branchHiddenAsignar" required>
                </div>

                <!-- === GRADO Y NIVEL === -->
                <div>
                    <label>Grado</label>
                    <select name="grade" required>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
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

                <!-- === AÑO === -->
                <div>
                    <label>Año</label>
                    <input type="number" name="anio" value="{{ date('Y') }}" readonly
                           style="background:rgba(255,255,255,0.05); color:var(--muted);">
                </div>

                <!-- === CICLO === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Ciclo</label>
                    <div class="custom-select" id="selectCicloCustom">
                        <div class="selected-option">Seleccione ciclo...</div>
                        <div class="options-list">
                            <div class="options-container">
                                @for($i = 1; $i <= 10; $i++)
                                    <div class="option" data-value="{{ $i }}">Ciclo {{ $i }}</div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="ciclo" id="cicloHidden" required>
                </div>

                <!-- === HORARIO (estilo final sin botón blanco) === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Horario</label>
                    <div class="custom-select horario-input" id="horarioEditableCustom">
                        <div class="selected-option" id="horarioSelected">
                            <span id="horarioTexto">Selecciona el horario...</span>
                            <i class="fa-solid fa-clock reloj-icono"></i>
                        </div>
                        <div class="options-list">
                            <input type="text" id="inputHorario" name="horario"
                                   placeholder="Escribe o busca horario..."
                                   autocomplete="off" required>
                            <div class="options-container">
                                <div class="option">De 7:00 a 9:00</div>
                                <div class="option">De 9:00 a 11:00</div>
                                <div class="option">De 11:00 a 13:00</div>
                                <div class="option">De 14:00 a 16:00</div>
                                <div class="option">De 16:00 a 18:00</div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- === CUPO === -->
                <div>
                    <label>Cupo</label>
                    <input type="number" name="cupo" id="inputCupo" value="30" min="10" max="40" required>
                </div>

                <!-- === BOTONES === -->
                <div class="modal-actions" style="grid-column: span 2;">
                    <button type="submit" class="btn-confirm">Asignar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalAsignarCurso')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- === MODAL ELIMINAR === -->
    <div id="modalEliminarCatedratico" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación</h3>
            <p>¿Estás seguro de que deseas eliminar este catedrático? Esta acción no se puede deshacer.</p>
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
                @if(isset($teachers) && $teachers->count())
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
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody id="tablaCursosAsignados">
                        <!-- Esta tabla se llenará dinámicamente con JS -->
                        <tr><td colspan="9" style="text-align:center;color:var(--muted);">Selecciona un catedrático para ver sus cursos.</td></tr>
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalCursos')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- === MODAL EDITAR ASIGNACIÓN DE CURSO === -->
    <div id="modalEditarAsignacion" class="modal-overlay">
        <div class="modal-content" style="max-width:580px;">
            <h3><i class="fa-solid fa-pen"></i> Editar Asignación de Curso</h3>

            <form id="formEditarAsignacion" class="form-modal form-grid-2">
                @csrf
                @method('PUT')
                <input type="hidden" name="offering_id" id="editOfferingId">

                <!-- === GRADO Y NIVEL === -->
                <div>
                    <label>Grado</label>
                    <select name="grade" id="editGrade" required>
                        <option value="Novatos">Novatos</option>
                        <option value="Expertos">Expertos</option>
                    </select>
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

                <!-- === CICLO PERSONALIZADO === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Ciclo</label>
                    <div class="custom-select" id="selectCicloEdit">
                        <div class="selected-option">Selecciona ciclo...</div>
                        <div class="options-list">
                            <div class="options-container">
                                @for($i = 1; $i <= 10; $i++)
                                    <div class="option" data-value="{{ $i }}">Ciclo {{ $i }}</div>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="ciclo" id="cicloHiddenEdit" required>
                </div>

                <!-- === CUPO (con validación de 10 a 40) === -->
                <div>
                    <label>Cupo</label>
                    <input type="number" name="cupo" id="editCupo" value="30" min="10" max="40" required>
                </div>

                <!-- === HORARIO PERSONALIZADO (idéntico al de asignar curso) === -->
                <div class="custom-select-wrapper campo-full">
                    <label>Horario</label>
                    <div class="custom-select horario-input" id="horarioEditableEdit">
                        <div class="selected-option" id="horarioSelectedEdit">
                            <span id="horarioTextoEdit">Selecciona el horario...</span>
                        </div>
                        <div class="options-list">
                            <input type="text" id="inputHorarioEdit" name="horario"
                                   placeholder="Escribe o busca horario..."
                                   autocomplete="off" required>
                            <div class="options-container">
                                <div class="option">De 7:00 a 9:00</div>
                                <div class="option">De 9:00 a 11:00</div>
                                <div class="option">De 11:00 a 13:00</div>
                                <div class="option">De 14:00 a 16:00</div>
                                <div class="option">De 16:00 a 18:00</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- === BOTONES === -->
                <div class="modal-actions" style="grid-column: span 2;">
                    <button type="submit" class="btn-confirm">Guardar cambios</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarAsignacion')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>


    <!-- === MODAL ELIMINAR ASIGNACIÓN DE CURSO === -->
    <div id="modalEliminarAsignacion" class="modal-overlay">
        <div class="modal-content">
            <h3 style="color:#FF5C5C;">
                <i class="fa-solid fa-triangle-exclamation"></i> Confirmar eliminación
            </h3>
            <p>¿Estás seguro de que deseas eliminar esta asignación de curso? Esta acción no se puede deshacer.</p>
            <form method="POST" id="formEliminarAsignacion">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Eliminar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal('modalEliminarAsignacion')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        @if (session('success'))
            window.sessionSuccess = "{{ session('success') }}";
        @endif
            @if (session('updated'))
            window.sessionUpdated = "{{ session('updated') }}";
        @endif
            @if (session('deleted'))
            window.sessionDeleted = "{{ session('deleted') }}";
        @endif
    </script>

    <script src="{{ asset('js/admin_catedraticos.js') }}"></script>
    <script>
        createCustomSelect('selectCursoCustom', 'courseHidden', 'filterCursos');
        createCustomSelect('selectSucursalAsignar', 'branchHiddenAsignar', 'filterSucursalAsignar');
        createCustomSelect('selectCicloCustom', 'cicloHidden', null);
        createCustomSelect('selectHorarioCustom', 'horarioHidden', null);

    </script>

@endsection
