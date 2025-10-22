/* =======================================================
 💡 SECRETARÍA — CATEDRÁTICOS (JS)
 🔹 CRUD con validaciones en tiempo real
 🔹 Notificaciones flotantes y UX consistente
========================================================== */

// === MODALES ===
const modalNuevo = document.getElementById('modalNuevoCatedratico');
const modalEditar = document.getElementById('modalEditarCatedratico');
const modalEliminar = document.getElementById('modalEliminarCatedratico');
const modalCursos = document.getElementById('modalCursos');
const formEditar = document.getElementById('formEditarCatedratico');
const formEliminar = document.getElementById('formEliminarCatedratico');
const btnNuevo = document.getElementById('btnNuevoCatedratico');

if (btnNuevo) btnNuevo.addEventListener('click', () => modalNuevo.classList.add('show'));

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
                    type === 'warning' ? '#f1c40f' : '#3498db',
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

// === BLOQUEO EN TIEMPO REAL (nombre y teléfono) ===
function bloquearCampos() {
    // Nombre: solo letras y espacios
    document.querySelectorAll('input[name="nombres"], #editNombres').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });

    // Teléfono: solo números, máximo 8
    document.querySelectorAll('input[name="telefono"], #editTelefono').forEach(input => {
        input.setAttribute('maxlength', '8');
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, ''); // elimina no numéricos
            if (input.value.length > 8) {
                input.value = input.value.slice(0, 8);
            }
        });
    });
}

bloquearCampos();

// === VALIDACIONES AL ENVIAR FORMULARIOS ===
const formNuevo = modalNuevo?.querySelector('form');
if (formNuevo) {
    formNuevo.addEventListener('submit', e => {
        const nombre = formNuevo.querySelector('input[name="nombres"]').value.trim();
        const telefono = formNuevo.querySelector('input[name="telefono"]').value.trim();

        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre) || nombre.length < 3) {
            e.preventDefault();
            showFloatingAlert('❌ El nombre solo debe contener letras y tener al menos 3 caracteres.', 'error');
            return;
        }

        if (!/^[0-9]{8}$/.test(telefono)) {
            e.preventDefault();
            showFloatingAlert('⚠️ El teléfono debe contener exactamente 8 números.', 'warning');
            return;
        }

        showFloatingAlert('✅ Catedrático guardado correctamente.', 'success');
    });
}

if (formEditar) {
    formEditar.addEventListener('submit', e => {
        const nombre = document.getElementById('editNombres').value.trim();
        const telefono = document.getElementById('editTelefono').value.trim();

        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre) || nombre.length < 3) {
            e.preventDefault();
            showFloatingAlert('❌ El nombre ingresado no es válido.', 'error');
            return;
        }

        if (!/^[0-9]{8}$/.test(telefono)) {
            e.preventDefault();
            showFloatingAlert('⚠️ El teléfono debe tener exactamente 8 números.', 'warning');
            return;
        }

        showFloatingAlert('✏️ Actualizando información...', 'success');
    });
}

// === EDITAR ===
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('editNombres').value = btn.dataset.nombres;
        document.getElementById('editTelefono').value = btn.dataset.telefono || '';
        document.getElementById('editBranch').value = btn.dataset.branch;
        formEditar.action = `/secretaria/catedraticos/${id}`;
        modalEditar.classList.add('show');
    });
});

// === ELIMINAR ===
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        formEliminar.action = `/secretaria/catedraticos/${id}`;
        modalEliminar.classList.add('show');
    });
});
formEliminar?.addEventListener('submit', () => {
    showFloatingAlert('🗑️ Catedrático eliminado correctamente.', 'success');
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
            tabla.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--muted)">
                Este catedrático no tiene cursos asignados.
            </td></tr>`;
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
                    </tr>`;
            });
        }
        modal.classList.add('show');
    });
});

// === BUSCAR Y ORDENAR ===
const buscar = document.getElementById('buscarCatedratico');
const orden = document.getElementById('ordenCatedraticos');
[buscar, orden].forEach(el => el?.addEventListener('input', filtrarYOrdenar));

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
document.querySelector('.actions-right').insertBefore(
    filtroCursos,
    document.getElementById('btnNuevoCatedratico')
);

filtroCursos.addEventListener('change', () => {
    const valor = filtroCursos.value;
    document.querySelectorAll('#tablaCatedraticos tr').forEach(fila => {
        const cursosCelda = fila.querySelector('.cursos');
        const tieneCursos = cursosCelda && !cursosCelda.textContent.includes('Sin asignaciones');
        fila.style.display =
            valor === 'todos' ||
            (valor === 'con' && tieneCursos) ||
            (valor === 'sin' && !tieneCursos)
                ? ''
                : 'none';
    });
});

// === CERRAR MODALES ===
window.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        [modalNuevo, modalEditar, modalEliminar, modalCursos].forEach(m => m.classList.remove('show'));
});

function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.querySelectorAll('.modal-overlay').forEach(o =>
    o.addEventListener('click', e => {
        if (e.target === o) cerrarModal(o.id);
    })
);


    function setupCustomSelect(selectId, hiddenId, filterId) {
    const customSelect = document.getElementById(selectId);
    const selected = customSelect.querySelector('.selected-option');
    const optionsList = customSelect.querySelector('.options-list');
    const optionsContainer = customSelect.querySelector('.options-container');
    const hiddenInput = document.getElementById(hiddenId);
    const filterInput = document.getElementById(filterId);

    // Normalizar texto para búsqueda
    function normalizeText(str) {
    return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

    // Abrir/cerrar el menú
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
    selected.textContent = opt.querySelector('.opt-main').textContent.trim();
    hiddenInput.value = opt.dataset.value;
    customSelect.classList.remove('open');
});
});

    // Buscar en tiempo real
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

    // Activar para ambos selectores
    setupCustomSelect('selectUsuarioCustom', 'usuarioHidden', 'filterUsuarios');
    setupCustomSelect('selectSucursalCustom', 'sucursalHidden', 'filterSucursales');


