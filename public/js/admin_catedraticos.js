/* =======================================================
 💡 ADMIN — CATEDRÁTICOS (JS FINAL)
 🔹 CRUD completo con validaciones, filtros y custom selects
========================================================= */

// === ALERTAS FLOTANTES (MEJORADAS) ===
function showFloatingAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.classList.add('floating-alert', `alert-${type}`);
    alert.innerHTML = message;

    // Estilos base
    Object.assign(alert.style, {
        position: 'fixed',
        top: '20px',
        left: '50%',
        transform: 'translateX(-50%)',
        padding: '12px 24px',
        borderRadius: '10px',
        color: '#fff',
        fontWeight: '500',
        fontSize: '0.95rem',
        zIndex: '9999',
        opacity: '0',
        transition: 'opacity .3s ease, transform .3s ease',
        boxShadow: '0 6px 20px rgba(0,0,0,0.4)',
        backdropFilter: 'blur(6px)',
        textAlign: 'center',
        maxWidth: '90%',
    });

    // Colores por tipo
    switch (type) {
        case 'success':
            alert.style.background = 'linear-gradient(135deg, #00b09b, #96c93d)'; // verde
            break;
        case 'error':
            alert.style.background = 'linear-gradient(135deg, #ff416c, #ff4b2b)'; // rojo
            break;
        case 'warning':
            alert.style.background = 'linear-gradient(135deg, #f7971e, #ffd200)'; // amarillo
            alert.style.color = '#000';
            break;
        case 'info':
        default:
            alert.style.background = 'linear-gradient(135deg, #56ccf2, #2f80ed)'; // azul
            break;
    }

    // Animación de entrada
    document.body.appendChild(alert);
    setTimeout(() => {
        alert.style.opacity = '1';
        alert.style.transform = 'translateX(-50%) translateY(0)';
    }, 50);

    // Desaparición automática
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(() => alert.remove(), 400);
    }, 4000);
}


// === MENSAJES DE SESIÓN (para crear/editar/eliminar) ===
document.addEventListener("DOMContentLoaded", () => {
    if (window.sessionSuccess) showFloatingAlert(window.sessionSuccess, 'success');
    if (window.sessionUpdated) showFloatingAlert(window.sessionUpdated, 'success');
    if (window.sessionDeleted) showFloatingAlert(window.sessionDeleted, 'warning');
});

// === MODALES PRINCIPALES ===
const modalNuevo = document.getElementById('modalNuevoCatedratico');
const modalEditar = document.getElementById('modalEditarCatedratico');
const modalEliminar = document.getElementById('modalEliminarCatedratico');

document.getElementById('btnNuevoCatedratico').addEventListener('click', () => modalNuevo.classList.add('show'));

// === EDITAR ===
const formEditar = document.getElementById('formEditarCatedratico');
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const nombre = btn.dataset.nombres;
        const telefono = btn.dataset.telefono;
        const branch = btn.dataset.branch;

        formEditar.action = `/administrador/catedraticos/${id}`;
        document.getElementById('editNombres').value = nombre;
        document.getElementById('editTelefono').value = telefono;

        // ✅ Custom select de sucursal en editar
        const select = document.getElementById('selectSucursalEdit');
        const selected = select.querySelector('.selected-option');
        const hidden = document.getElementById('branchHiddenEdit');
        const options = select.querySelectorAll('.option');

        options.forEach(opt => {
            if (opt.dataset.value === branch) {
                selected.textContent = opt.querySelector('.opt-main')?.textContent || opt.textContent.trim();
                hidden.value = branch;
            }
        });

        modalEditar.classList.add('show');
    });
});

// === VALIDACIONES ===
// Nombres: solo letras y espacios
document.querySelectorAll('input[name="nombres"], #editNombres').forEach(input => {
    input.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
});

// Teléfono: solo números, máximo 8 dígitos
document.querySelectorAll('input[name="telefono"], #editTelefono').forEach(input => {
    input.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value.length > 8) e.target.value = e.target.value.slice(0, 8);
    });
});

// Validar en submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', e => {
        const telefonoInput = form.querySelector('input[name="telefono"]');
        if (telefonoInput) {
            const valor = telefonoInput.value.trim();
            if (valor !== '' && !/^[0-9]{8}$/.test(valor)) {
                e.preventDefault();
                showFloatingAlert('❌ El teléfono debe tener exactamente 8 dígitos numéricos.', 'error');
            }
        }
    });
});

// === FILTROS Y ORDEN ===
const buscar = document.getElementById('buscarCatedratico');
const orden = document.getElementById('ordenCatedraticos');

if (buscar && orden) {
    buscar.addEventListener('input', filtrarYOrdenar);
    orden.addEventListener('change', filtrarYOrdenar);
}

function filtrarYOrdenar() {
    const texto = buscar.value.toLowerCase();
    const ordenSeleccionado = orden.value;
    const filas = Array.from(document.querySelectorAll('#tablaCatedraticos tr'));

    filas.forEach(fila => {
        const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase() || '';
        const usuario = fila.querySelector('.usuario')?.textContent.toLowerCase() || '';
        const visible = nombre.includes(texto) || usuario.includes(texto);
        fila.style.display = visible ? '' : 'none';
    });

    const visibles = filas.filter(f => f.style.display !== 'none');
    visibles.sort((a, b) => {
        const nA = a.querySelector('.nombre')?.textContent.toLowerCase() || '';
        const nB = b.querySelector('.nombre')?.textContent.toLowerCase() || '';
        const idA = parseInt(a.children[0].textContent);
        const idB = parseInt(b.children[0].textContent);
        if (ordenSeleccionado === 'alfabetico') return nA.localeCompare(nB);
        if (ordenSeleccionado === 'inverso') return nB.localeCompare(nA);
        if (ordenSeleccionado === 'antiguos') return idA - idB;
        if (ordenSeleccionado === 'recientes') return idB - idA;
        return 0;
    });

    const tbody = document.getElementById('tablaCatedraticos');
    visibles.forEach(f => tbody.appendChild(f));
}

// === ELIMINAR CATEDRÁTICO ===
const formEliminar = document.getElementById('formEliminarCatedratico');
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        formEliminar.action = `/administrador/catedraticos/${id}`;
        modalEliminar.classList.add('show');
    });
});

// === ASIGNAR CURSO ===
const modalAsignar = document.getElementById('modalAsignarCurso');
const formAsignar = document.getElementById('formAsignarCurso');
document.querySelectorAll('.btn-assign').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        formAsignar.action = `/administrador/catedraticos/${id}/asignar-curso`;
        modalAsignar.classList.add('show');
    });
});

// === VER CURSOS ===
document.querySelectorAll('.btn-ver-cursos').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.getElementById('modalCursos');
        const tabla = document.getElementById('tablaCursosAsignados');
        const nombreCat = document.getElementById('nombreCatedratico');
        const cursos = JSON.parse(btn.dataset.cursos || '[]');

        nombreCat.textContent = btn.dataset.nombre;
        tabla.innerHTML = '';

        if (cursos.length === 0) {
            tabla.innerHTML = `<tr><td colspan="9" style="text-align:center;color:var(--muted)">Este catedrático no tiene cursos asignados.</td></tr>`;
        } else {
            cursos.forEach(c => {
                tabla.innerHTML += `
                    <tr>
                        <td>${c.course?.nombre ?? '—'}</td>
                        <td>${c.grade ?? '—'}</td>
                        <td>${c.level ?? '—'}</td>
                        <td>${c.branch?.nombre ?? '—'}</td>
                        <td>${c.anio ?? '—'}</td>
                        <td>${c.ciclo ?? '—'}</td>
                        <td>${c.cupo ?? '—'}</td>
                        <td>${c.horario ?? '—'}</td>
                        <td class="acciones">
                            <button type="button" class="btn-edit-mini"
                                onclick="editarAsignacion(${c.id}, '${c.grade}', '${c.level}', '${c.ciclo}', ${c.cupo}, '${c.horario ?? ''}')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" class="btn-delete-mini"
                                onclick="abrirModalEliminarAsignacion(${c.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        }
        modal.classList.add('show');
    });
});

// === EDITAR ASIGNACIÓN ===
function editarAsignacion(id, grade, level, ciclo, cupo, horario) {
    const modal = document.getElementById('modalEditarAsignacion');
    const form = document.getElementById('formEditarAsignacion');

    document.getElementById('editOfferingId').value = id;
    document.getElementById('editGrade').value = grade;
    document.getElementById('editLevel').value = level;
    document.getElementById('cicloHiddenEdit').value = ciclo;
    document.getElementById('editCupo').value = cupo;
    document.getElementById('inputHorarioEdit').value = horario;
    document.getElementById('horarioTextoEdit').textContent = horario || 'Selecciona el horario...';

    // ✅ Actualizar texto del custom select de ciclo
    const cicloSelect = document.getElementById('selectCicloEdit');
    const selected = cicloSelect.querySelector('.selected-option');
    selected.textContent = `Ciclo ${ciclo}`;

    form.action = `/administrador/asignacion/${id}`;
    form.method = 'POST';

    if (!form.querySelector('input[name="_method"]')) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = '_method';
        hidden.value = 'PUT';
        form.appendChild(hidden);
    }

    modal.classList.add('show');
}


// === ELIMINAR ASIGNACIÓN ===
function abrirModalEliminarAsignacion(id) {
    const modal = document.getElementById('modalEliminarAsignacion');
    const form = document.getElementById('formEliminarAsignacion');
    form.action = `/administrador/asignacion/${id}`;
    modal.classList.add('show');
}

// === CUSTOM SELECT GENERAL ===
function createCustomSelect(selectId, hiddenId, filterId) {
    const select = document.getElementById(selectId);
    const selected = select.querySelector('.selected-option');
    const optionsList = select.querySelector('.options-list');
    const optionsContainer = select.querySelector('.options-container');
    const hiddenInput = document.getElementById(hiddenId);
    const filterInput = document.getElementById(filterId);

    const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

    selected.addEventListener('click', () => {
        select.classList.toggle('open');
        filterInput.value = '';
        filterOptions('');
        if (select.classList.contains('open')) setTimeout(() => filterInput.focus(), 150);
    });

    optionsContainer.querySelectorAll('.option').forEach(opt => {
        opt.addEventListener('click', () => {
            selected.textContent = opt.querySelector('.opt-main')?.textContent || opt.textContent.trim();
            hiddenInput.value = opt.dataset.value;
            select.classList.remove('open');
        });
    });

    function filterOptions(term) {
        const val = normalize(term);
        optionsContainer.querySelectorAll('.option').forEach(opt => {
            const text = normalize(opt.textContent);
            opt.style.display = text.includes(val) ? 'block' : 'none';
        });
    }
    filterInput.addEventListener('input', e => filterOptions(e.target.value));
    window.addEventListener('click', e => { if (!select.contains(e.target)) select.classList.remove('open'); });
}


// === CERRAR MODALES ===
window.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
});
function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) cerrarModal(o.id); });
});
// === VALIDAR RANGO DE CUPO (10 - 40) ===
const cupoInput = document.querySelector('#formAsignarCurso input[name="cupo"]');
if (cupoInput) {
    cupoInput.addEventListener('input', e => {
        let valor = parseInt(e.target.value) || 0;
        if (valor < 10) valor = 10;
        if (valor > 40) valor = 40;
        e.target.value = valor;
    });
}



// === HORARIO (para editar asignación también) ===
function configurarHorario(boxId, textoId, inputId) {
    const box = document.getElementById(boxId);
    if (!box) return;

    const texto = document.getElementById(textoId);
    const input = document.getElementById(inputId);
    const options = box.querySelectorAll('.option');
    const selected = box.querySelector('.selected-option');

    selected.addEventListener('click', () => {
        box.classList.toggle('open');
        input.focus();
    });

    input.addEventListener('input', e => {
        const term = e.target.value.toLowerCase();
        options.forEach(opt => {
            const t = opt.textContent.toLowerCase();
            opt.style.display = t.includes(term) ? 'block' : 'none';
        });
    });

    options.forEach(opt => {
        opt.addEventListener('click', () => {
            texto.textContent = opt.textContent;
            input.value = opt.textContent;
            box.classList.remove('open');
        });
    });

    document.addEventListener('click', e => {
        if (!box.contains(e.target)) box.classList.remove('open');
    });
}

// ✅ Inicializar los dos select de horario
configurarHorario('horarioEditableCustom', 'horarioTexto', 'inputHorario');
configurarHorario('horarioEditableEdit', 'horarioTextoEdit', 'inputHorarioEdit');

// === Inicializar todos los custom selects ===
createCustomSelect('selectUsuarioCustom', 'userHidden', 'filterUsuarios');
createCustomSelect('selectSucursalCustom', 'branchHidden', 'filterSucursales');
createCustomSelect('selectSucursalEdit', 'branchHiddenEdit', 'filterSucursalesEdit');
createCustomSelect('selectCicloEdit', 'cicloHiddenEdit', null);

/* =======================================================
 🧭 ALERTAS FLOTANTES — ACCIONES CRUD Y RESTRICCIONES
========================================================= */

// ✅ 1️⃣ CATEDRÁTICO con asignaciones activas
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;

        try {
            const response = await fetch(`/administrador/catedraticos/${id}/verificar-asignaciones`);
            const data = await response.json();

            if (data.tiene_asignaciones) {
                showFloatingAlert('⚠️ No se puede eliminar el catedrático: tiene cursos asignados.', 'warning');
                return; // No abre el modal
            }

            // Si no tiene asignaciones, sí abre el modal
            const formEliminar = document.getElementById('formEliminarCatedratico');
            formEliminar.action = `/administrador/catedraticos/${id}`;
            document.getElementById('modalEliminarCatedratico').classList.add('show');

        } catch (error) {
            console.error(error);
            showFloatingAlert('❌ Error al verificar las asignaciones del catedrático.', 'error');
        }
    });
});


// ✅ 2️⃣ ELIMINAR ASIGNACIÓN CON ALUMNOS
document.querySelectorAll('.btn-delete-mini').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.closest('button').getAttribute('onclick').match(/\d+/)[0];

        try {
            const response = await fetch(`/administrador/asignacion/${id}/verificar-alumnos`);
            const data = await response.json();

            if (data.tiene_alumnos) {
                showFloatingAlert('⚠️ No se puede eliminar: esta asignación tiene alumnos inscritos.', 'warning');
                return;
            }

            // Si no tiene alumnos, abre modal de eliminación
            const form = document.getElementById('formEliminarAsignacion');
            form.action = `/administrador/asignacion/${id}`;
            document.getElementById('modalEliminarAsignacion').classList.add('show');

        } catch (error) {
            console.error(error);
            showFloatingAlert('❌ Error al verificar alumnos inscritos en la asignación.', 'error');
        }
    });
});


// ✅ 3️⃣ CREAR / EDITAR / ELIMINAR — Mensajes genéricos inmediatos
// (Para formularios normales que no recargan la página)
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', e => {
        if (form.classList.contains('form-modal')) {
            const tipo = form.id.includes('Editar') ? 'update' : form.id.includes('Eliminar') ? 'delete' : 'create';

            switch (tipo) {
                case 'create':
                    showFloatingAlert('✅ Catedrático registrado correctamente.', 'success');
                    break;
                case 'update':
                    showFloatingAlert('✏️ Cambios guardados correctamente.', 'success');
                    break;
                case 'delete':
                    showFloatingAlert('🗑️ Eliminación completada.', 'warning');
                    break;
            }
        }
    });
});


// ✅ 4️⃣ VALIDACIONES EXTRA DE CAMPOS EN TIEMPO REAL
// (Evita enviar formularios vacíos o fuera de rango)
const forms = document.querySelectorAll('.form-modal');
forms.forEach(f => {
    f.addEventListener('submit', e => {
        const cupo = f.querySelector('input[name="cupo"]');
        const horario = f.querySelector('input[name="horario"]');
        const ciclo = f.querySelector('input[name="ciclo"]');
        const nombre = f.querySelector('input[name="nombres"]');

        if (cupo && (parseInt(cupo.value) < 10 || parseInt(cupo.value) > 40)) {
            e.preventDefault();
            showFloatingAlert('⚠️ El cupo debe estar entre 10 y 40 alumnos.', 'warning');
            return;
        }

        if (horario && horario.value.trim() === '') {
            e.preventDefault();
            showFloatingAlert('⚠️ Debes seleccionar o escribir un horario.', 'warning');
            return;
        }

        if (ciclo && ciclo.value.trim() === '') {
            e.preventDefault();
            showFloatingAlert('⚠️ Debes elegir un ciclo válido.', 'warning');
            return;
        }

        if (nombre && nombre.value.trim() === '') {
            e.preventDefault();
            showFloatingAlert('⚠️ El nombre no puede estar vacío.', 'warning');
            return;
        }
    });
});
