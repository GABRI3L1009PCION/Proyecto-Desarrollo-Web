/* =======================================================
 💡 ADMIN — CATEDRÁTICOS (JS)
 🔹 CRUD clásico con validaciones, alertas y select de horario
========================================================= */

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
                type === 'warning' ? '#f1c40f' :
                    '#2ecc71',
        color: '#fff',
        padding: '10px 20px',
        borderRadius: '8px',
        boxShadow: '0 4px 10px rgba(0,0,0,0.3)',
        zIndex: '9999',
        opacity: '0',
        transition: 'opacity .3s ease'
    });
    document.body.appendChild(alert);
    setTimeout(() => alert.style.opacity = '1', 50);
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 400);
    }, 3500);
}

// === NUEVO ===
const modalNuevo = document.getElementById('modalNuevoCatedratico');
document.getElementById('btnNuevoCatedratico').addEventListener('click', () => modalNuevo.classList.add('show'));

// === EDITAR ===
const modalEditar = document.getElementById('modalEditarCatedratico');
const formEditar = document.getElementById('formEditarCatedratico');
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        formEditar.action = `/administrador/catedraticos/${id}`;
        document.getElementById('editNombres').value = btn.dataset.nombres;
        document.getElementById('editTelefono').value = btn.dataset.telefono;
        document.getElementById('editBranch').value = btn.dataset.branch;
        modalEditar.classList.add('show');
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

// === VER CURSOS (normal) ===
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

// === FUNCIÓN PARA LLENAR Y ENVIAR EL MODAL DE EDICIÓN DE ASIGNACIÓN ===
function editarAsignacion(id, grade, level, ciclo, cupo, horario) {
    const modal = document.getElementById('modalEditarAsignacion');
    const form = document.getElementById('formEditarAsignacion');

    document.getElementById('editOfferingId').value = id;
    document.getElementById('editGrade').value = grade;
    document.getElementById('editLevel').value = level;
    document.getElementById('editCiclo').value = ciclo;
    document.getElementById('editCupo').value = cupo;
    document.getElementById('editHorario').value = horario;

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

// === MODAL ELIMINAR ASIGNACIÓN ===
function abrirModalEliminarAsignacion(id) {
    const modal = document.getElementById('modalEliminarAsignacion');
    const form = document.getElementById('formEliminarAsignacion');
    form.action = `/administrador/asignacion/${id}`;
    modal.classList.add('show');
}

// === ELIMINAR CATEDRÁTICO ===
const modalEliminar = document.getElementById('modalEliminarCatedratico');
const formEliminar = document.getElementById('formEliminarCatedratico');
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        formEliminar.action = `/administrador/catedraticos/${id}`;
        modalEliminar.classList.add('show');
    });
});

// === VALIDACIONES ===
// Teléfono (solo números y máximo 8 dígitos)
document.querySelectorAll('input[name="telefono"], #editTelefono').forEach(input => {
    input.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value.length > 8) e.target.value = e.target.value.slice(0, 8);
    });
});

// Cupo (máximo 30)
document.querySelectorAll('input[name="cupo"], #editCupo').forEach(input => {
    input.addEventListener('input', e => {
        let val = parseInt(e.target.value);
        if (val > 30) {
            e.target.value = 30;
            showFloatingAlert('⚠️ El cupo máximo permitido es 30.', 'warning');
        }
        if (val < 1) e.target.value = 1;
    });
});

// === SELECT DE HORARIOS (se guarda como texto) ===
function crearDropdownHorario(input) {
    if (!input) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'dropdown-custom';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'dropdown-btn';
    btn.textContent = input.value || 'Selecciona horario...';

    const list = document.createElement('ul');
    list.className = 'dropdown-list';
    const horarios = [
        'De 7:00 a 9:00',
        'De 9:00 a 11:00',
        'De 11:00 a 13:00',
        'De 14:00 a 16:00',
        'De 16:00 a 18:00'
    ];

    horarios.forEach(h => {
        const li = document.createElement('li');
        li.textContent = h;
        li.addEventListener('click', () => {
            input.value = h;
            btn.textContent = h;
            list.style.display = 'none';
        });
        list.appendChild(li);
    });

    wrapper.appendChild(btn);
    wrapper.appendChild(list);
    input.style.display = 'none';
    input.parentElement.appendChild(wrapper);

    btn.addEventListener('click', () => {
        list.style.display = list.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', e => {
        if (!wrapper.contains(e.target)) list.style.display = 'none';
    });
}

crearDropdownHorario(document.querySelector('#formAsignarCurso input[name="horario"]'));
crearDropdownHorario(document.getElementById('editHorario'));

// estilos dropdown
const style = document.createElement('style');
style.textContent = `
.dropdown-custom { position: relative; width: 100%; }
.dropdown-btn {
    width: 100%; background: rgba(255,255,255,0.05);
    color: #fff; border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px; padding: 8px 10px; text-align: left; cursor: pointer;
}
.dropdown-btn:hover { background: rgba(255,255,255,0.08); }
.dropdown-list {
    position: absolute; top: 100%; left: 0; width: 100%;
    background: rgba(10,20,45,0.98); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; margin-top: 4px; max-height: 160px; overflow-y: auto;
    display: none; z-index: 9999;
}
.dropdown-list li { padding: 8px 12px; color: #fff; cursor: pointer; }
.dropdown-list li:hover { background: rgba(78,156,255,0.3); }
`;
document.head.appendChild(style);

// === CERRAR MODALES ===
window.addEventListener('keydown', e => {
    if (e.key === 'Escape') [
        modalNuevo, modalEditar, modalEliminar, modalAsignar,
        document.getElementById('modalCursos'),
        document.getElementById('modalEditarAsignacion'),
        document.getElementById('modalEliminarAsignacion')
    ].forEach(m => m.classList.remove('show'));
});

function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.querySelectorAll('.modal-overlay').forEach(o =>
    o.addEventListener('click', e => {
        if (e.target === o) cerrarModal(o.id);
    })
);
