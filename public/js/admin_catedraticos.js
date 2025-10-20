/* =======================================================
 💡 ADMIN — CATEDRÁTICOS (JS)
 🔹 Versión 100% Web — CRUD, asignaciones y modales
========================================================= */

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

// === VER CURSOS (versión 100% web con acciones) ===
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

// === MODAL ELIMINAR ASIGNACIÓN (nuevo modal moderno) ===
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

// === BUSCAR Y ORDENAR ===
const buscar = document.getElementById('buscarCatedratico');
const orden = document.getElementById('ordenCatedraticos');
[buscar, orden].forEach(el => el.addEventListener('input', filtrarYOrdenar));

function filtrarYOrdenar() {
    const texto = buscar.value.toLowerCase();
    const ordenSel = orden.value;
    const filas = Array.from(document.querySelectorAll('#tablaCatedraticos tr'));

    filas.forEach(fila => {
        const nombre = fila.querySelector('.nombre')?.textContent.toLowerCase();
        fila.style.display = (!texto || nombre.includes(texto)) ? '' : 'none';
    });

    const visibles = filas.filter(f => f.style.display !== 'none');
    visibles.sort((a, b) => {
        const nA = a.querySelector('.nombre').textContent.toLowerCase();
        const nB = b.querySelector('.nombre').textContent.toLowerCase();
        const idA = parseInt(a.children[0].textContent);
        const idB = parseInt(b.children[0].textContent);
        if (ordenSel === 'alfabetico') return nA.localeCompare(nB);
        if (ordenSel === 'inverso') return nB.localeCompare(nA);
        if (ordenSel === 'antiguos') return idA - idB;
        if (ordenSel === 'recientes') return idB - idA;
        return 0;
    });

    const tbody = document.getElementById('tablaCatedraticos');
    visibles.forEach(f => tbody.appendChild(f));
}

// === FILTRO POR CURSOS ===
const filtroCursos = document.createElement('select');
filtroCursos.id = 'filtroCursos';
filtroCursos.innerHTML = `
    <option value="todos">Todos</option>
    <option value="con">Con cursos</option>
    <option value="sin">Sin cursos</option>
`;
document.querySelector('.actions-right').insertBefore(filtroCursos, document.getElementById('btnNuevoCatedratico'));
filtroCursos.addEventListener('change', aplicarFiltroCursos);

function aplicarFiltroCursos() {
    const valor = filtroCursos.value;
    document.querySelectorAll('#tablaCatedraticos tr').forEach(fila => {
        const cursosCelda = fila.querySelector('.cursos');
        const tieneCursos = cursosCelda && !cursosCelda.textContent.includes('Sin asignaciones');
        fila.style.display = (
            valor === 'todos' ||
            (valor === 'con' && tieneCursos) ||
            (valor === 'sin' && !tieneCursos)
        ) ? '' : 'none';
    });
}

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
