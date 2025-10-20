@extends('layouts.app')
@section('title', 'Calificaciones | Catedrático | Código Rapidito')

@section('content')
    <link rel="stylesheet" href="{{ asset('styles/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/catedratico_calificaciones.css') }}">
    <script src="https://kit.fontawesome.com/6e7086f99f.js" crossorigin="anonymous"></script>

    <div class="admin-wrapper">
        <!-- === SIDEBAR === -->
        <aside class="sidebar">
            <div class="logo-area">
                <img src="{{ asset('images/logo2.png') }}" alt="Logo">
                <h3>Código Rapidito</h3>
                <p class="role-tag">Catedrático</p>
            </div>

            <ul class="menu">
                <li class="menu-item {{ request()->routeIs('catedratico.panel') ? 'active' : '' }}">
                    <a href="{{ route('catedratico.panel') }}"><i class="fa-solid fa-gauge"></i> <span>Panel</span></a>
                </li>
                <li class="menu-item {{ request()->routeIs('catedratico.cursos') ? 'active' : '' }}">
                    <a href="{{ route('catedratico.cursos') }}"><i class="fa-solid fa-book"></i> <span>Mis Cursos</span></a>
                </li>
                <li class="menu-item active">
                    <a href="{{ route('catedratico.calificaciones') }}"><i class="fa-solid fa-pen-to-square"></i> <span>Calificaciones</span></a>
                </li>
            </ul>

            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</button>
            </form>
        </aside>

        <!-- === CONTENIDO PRINCIPAL === -->
        <main class="main-content">
            <header class="topbar">
                <h2><i class="fa-solid fa-pen-to-square"></i> Calificaciones</h2>
                <p class="welcome">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
            </header>

            <!-- === NOTIFICACIONES === -->
            @if(session('success'))
                <div class="toast toast-success" id="toastMsg">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="toast toast-error" id="toastMsg">{{ session('error') }}</div>
            @endif

            <!-- === SELECCIONAR CURSO === -->
            <section class="selector-curso">
                <label for="cursoSelect"><i class="fa-solid fa-book"></i> Selecciona un curso:</label>
                <select id="cursoSelect" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($cursos as $curso)
                        <option value="curso{{ $curso->id }}" {{ session('curso_activo') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->course->nombre }} — {{ $curso->grade }} ({{ $curso->level }})
                        </option>
                    @endforeach
                </select>
            </section>

            <!-- === TABLAS === -->
            @foreach($cursos as $curso)
                <section id="curso{{ $curso->id }}" class="curso-section" style="display:none;">
                    <h3><i class="fa-solid fa-list"></i> {{ $curso->course->nombre }} — {{ $curso->grade }} / {{ $curso->level }}</h3>

                    @php
                        $totalGeneral = $curso->enrollments->filter(fn($e) => $e->grade)->avg(fn($e) => $e->grade->total);
                        $aprobados = $curso->enrollments->filter(fn($e) => $e->grade && $e->grade->estado === 'Aprobado')->count();
                        $recuperacion = $curso->enrollments->filter(fn($e) => $e->grade && $e->grade->estado === 'Recuperación')->count();
                        $reprobados = $curso->enrollments->filter(fn($e) => $e->grade && $e->grade->estado === 'Reprobado')->count();
                    @endphp

                    <div class="resumen-curso">
                        <div><strong>Promedio general:</strong> {{ number_format($totalGeneral ?? 0,2) }} pts</div>
                        <div>
                            <span class="aprobados">Aprobados: {{ $aprobados }}</span> |
                            <span class="recuperacion">Recuperación: {{ $recuperacion }}</span> |
                            <span class="reprobados">Reprobados: {{ $reprobados }}</span>
                        </div>
                    </div>

                    <div class="filtros-section">
                        <input type="text" class="buscarAlumno" placeholder="Buscar alumno...">
                        <select class="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="Aprobado">Aprobados</option>
                            <option value="Recuperación">Recuperación</option>
                            <option value="Reprobado">Reprobados</option>
                        </select>
                    </div>

                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Alumno</th>
                            <th>Parcial 1</th>
                            <th>Parcial 2</th>
                            <th>Final</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($curso->enrollments as $index => $enrollment)
                            @php
                                $grade = $enrollment->grade;
                                $p1 = $grade->parcial1 ?? '';
                                $p2 = $grade->parcial2 ?? '';
                                $final = $grade->final ?? '';
                                $total = $grade->total ?? 0;
                                $estado = $grade->estado ?? '—';
                            @endphp
                            <tr data-estado="{{ $estado }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="nombre-alumno">{{ $enrollment->student->user->name }}</td>
                                <td>{{ $p1 ?: '—' }}</td>
                                <td>{{ $p2 ?: '—' }}</td>
                                <td>{{ $final ?: '—' }}</td>
                                <td>{{ number_format($total,2) }}</td>
                                <td class="estado {{ strtolower($estado) }}">{{ $estado }}</td>
                                <td>
                                    <form action="{{ route('catedratico.calificaciones.guardar') }}" method="POST" class="formNota">
                                        @csrf
                                        <input type="hidden" name="curso_id" value="{{ $curso->id }}">
                                        <button type="button" class="btn-modal"
                                                data-id="{{ $enrollment->id }}"
                                                data-nombre="{{ $enrollment->student->user->name }}"
                                                data-p1="{{ $p1 }}"
                                                data-p2="{{ $p2 }}"
                                                data-final="{{ $final }}">
                                            <i class="fa-solid fa-pen"></i> Calificar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach
        </main>
    </div>

    <!-- === MODAL === -->
    <div id="modalCalificacion" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3><i class="fa-solid fa-user-graduate"></i> Calificar Alumno</h3>

            <form id="formCalificar" action="{{ route('catedratico.calificaciones.guardar') }}" method="POST">
                @csrf
                <input type="hidden" id="cursoActivo" name="curso_id">
                <input type="hidden" id="enrollmentId" name="notas[ID][enrollment_id]">

                <div class="form-group">
                    <label>Alumno:</label>
                    <input type="text" id="nombreAlumno" readonly>
                </div>

                <div class="form-group">
                    <label>Parcial 1 (30 pts)</label>
                    <input type="number" id="parcial1" name="notas[ID][parcial1]" min="0" max="30" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Parcial 2 (30 pts)</label>
                    <input type="number" id="parcial2" name="notas[ID][parcial2]" min="0" max="30" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Examen Final (40 pts)</label>
                    <input type="number" id="final" name="notas[ID][final]" min="0" max="40" step="0.01" required>
                </div>

                <button type="submit" class="btn-guardar"><i class="fa-solid fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>

    <script>
        // === Mostrar curso seleccionado ===
        const selectCurso = document.getElementById('cursoSelect');
        selectCurso.addEventListener('change', e => {
            document.querySelectorAll('.curso-section').forEach(s => s.style.display = 'none');
            if (e.target.value) document.getElementById(e.target.value).style.display = 'block';
            sessionStorage.setItem('cursoActivo', e.target.value);
        });

        // Restaurar curso activo al recargar
        document.addEventListener('DOMContentLoaded', () => {
            const curso = "{{ session('curso_activo') }}";
            const cursoTemp = sessionStorage.getItem('cursoActivo');
            const seleccionado = curso || cursoTemp;
            if (seleccionado) {
                document.getElementById('cursoSelect').value = seleccionado;
                document.getElementById(seleccionado).style.display = 'block';
            }

            // Notificación flotante
            const toast = document.getElementById('toastMsg');
            if (toast) setTimeout(() => toast.remove(), 5000);
        });

        // === Modal ===
        const modal = document.getElementById('modalCalificacion');
        const closeBtn = document.querySelector('.modal .close');

        document.querySelectorAll('.btn-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.style.display = 'block';
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                const p1 = btn.dataset.p1;
                const p2 = btn.dataset.p2;
                const fin = btn.dataset.final ?? '';
                const cursoActivo = document.getElementById('cursoSelect').value.replace('curso', '');

                // Asignar datos
                document.getElementById('cursoActivo').value = cursoActivo;
                document.getElementById('enrollmentId').name = `notas[${id}][enrollment_id]`;
                document.getElementById('parcial1').name = `notas[${id}][parcial1]`;
                document.getElementById('parcial2').name = `notas[${id}][parcial2]`;
                document.getElementById('final').name = `notas[${id}][final]`;

                document.getElementById('nombreAlumno').value = nombre;
                document.getElementById('parcial1').value = p1;
                document.getElementById('parcial2').value = p2;
                document.getElementById('final').value = fin;

                const p1Input = document.getElementById('parcial1');
                const p2Input = document.getElementById('parcial2');
                const fInput = document.getElementById('final');

                // === LÓGICA CORRECTA DE DESBLOQUEO ===
                if (p1 === '' && p2 === '' && fin === '') {
                    // Solo se permite ingresar Parcial 1
                    p1Input.readOnly = false;
                    p2Input.disabled = true;
                    fInput.disabled = true;
                }
                else if (p1 !== '' && p2 === '' && fin === '') {
                    // Solo Parcial 2 habilitado
                    p1Input.readOnly = true;
                    p2Input.disabled = false;
                    fInput.disabled = true;
                }
                else if (p1 !== '' && p2 !== '' && fin === '') {
                    // Solo Final habilitado
                    p1Input.readOnly = true;
                    p2Input.disabled = true;
                    fInput.disabled = false;
                }
                else if (p1 !== '' && p2 !== '' && fin !== '') {
                    // Todos llenos → Final editable, parciales bloqueados
                    p1Input.readOnly = true;
                    p2Input.disabled = true;
                    fInput.disabled = false;
                }

                // === Validar límites ===
                [p1Input, p2Input, fInput].forEach(input => {
                    input.addEventListener('input', e => {
                        const val = parseFloat(e.target.value);
                        const min = parseFloat(e.target.min);
                        const max = parseFloat(e.target.max);
                        if (isNaN(val)) return;
                        if (val < min) e.target.value = min;
                        if (val > max) e.target.value = max;
                    });
                });
            });
        });

        closeBtn.onclick = () => modal.style.display = 'none';
        window.onclick = e => { if (e.target == modal) modal.style.display = 'none'; };

        // === Filtros ===
        document.querySelectorAll('.curso-section').forEach(section => {
            const filtroEstado = section.querySelector('.filtroEstado');
            const buscarAlumno = section.querySelector('.buscarAlumno');

            function filtrar() {
                const estado = filtroEstado.value.toLowerCase();
                const texto = buscarAlumno.value.toLowerCase();
                section.querySelectorAll('tbody tr').forEach(tr => {
                    const matchTexto = tr.querySelector('.nombre-alumno').innerText.toLowerCase().includes(texto);
                    const matchEstado = estado === '' || tr.dataset.estado.toLowerCase() === estado;
                    tr.style.display = (matchTexto && matchEstado) ? '' : 'none';
                });
            }

            filtroEstado.addEventListener('change', filtrar);
            buscarAlumno.addEventListener('input', filtrar);
        });

        // === Asegurar que todos los inputs se envíen ===
        document.getElementById('formCalificar').addEventListener('submit', () => {
            // Reactiva todos los inputs deshabilitados antes de enviar
            document.querySelectorAll('#formCalificar input:disabled').forEach(input => {
                input.disabled = false;
            });
        });

    </script>


    <style>
        .toast {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.8rem 1.4rem;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            z-index: 9999;
            animation: fadeInOut 5s ease forwards;
        }
        .toast-success {
            background: rgba(34,197,94,0.9);
            color: #fff;
        }
        .toast-error {
            background: rgba(239,68,68,0.9);
            color: #fff;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translate(-50%, -20px); }
            10%, 90% { opacity: 1; transform: translate(-50%, 0); }
            100% { opacity: 0; transform: translate(-50%, -20px); }
        }

        /* === ESTADOS VISUALES PARA CAMPOS BLOQUEADOS === */
        .modal-content input[readonly],
        .modal-content input:disabled {
            background: rgba(255,255,255,0.05);
            color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Pequeño brillo en los campos editables */
        .modal-content input:not([readonly]):not(:disabled):focus {
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 8px var(--primary-light, #4fc3f7);
            color: #fff;
        }

        /* Diferenciar las etiquetas según estado */
        .modal-content label {
            transition: color .3s ease;
        }
        .modal-content input[readonly] + label,
        .modal-content input:disabled + label {
            color: #64748b;
        }

    </style>
@endsection
