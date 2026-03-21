let tareas = JSON.parse(localStorage.getItem("docenteT")) || [];
let editIndex = null;

const alumnos = ["JUAN", "HECTOR", "LUIS"];

let entregas = {
    JUAN: { entrego: true, cal: 100 },
    HECTOR: { entrego: false, cal: 0 },
    LUIS: { entrego: true, cal: 80 }
};

/* NAVEGACIÓN */
function ir(pagina) {
    window.location.href = pagina;
}

/* GUARDAR LOCAL */
function guardarLocal() {
    localStorage.setItem("docenteT", JSON.stringify(tareas));
}

/* RENDER CARDS */
function render() {
    const cont = document.getElementById("cards");
    cont.innerHTML = "";

    tareas.forEach((t, i) => {
        cont.innerHTML += `
        <div class="card" onclick="verTarea()">
            <span class="edit-btn" onclick="event.stopPropagation(); editarTarea(${i})">✏️</span>
            <span class="delete-btn" onclick="event.stopPropagation(); eliminarTarea(${i})">✖</span>
            <strong>${t.titulo}</strong>
            <small>${t.materia} - ${t.grupo}</small>
        </div>`;
    });
}

/* VER TABLA */
function verTarea() {
    document.getElementById("cards").style.display = "none";
    document.getElementById("vistaTarea").classList.remove("hidden");
    renderTabla();
}

/* VOLVER */
function volver() {
    document.getElementById("cards").style.display = "flex";
    document.getElementById("vistaTarea").classList.add("hidden");
}

/* TABLA */
function renderTabla() {
    const tbody = document.getElementById("tablaTarea");
    tbody.innerHTML = "";

    alumnos.forEach(nombre => {
        const d = entregas[nombre];

        tbody.innerHTML += `
        <tr>
            <td>${nombre}</td>
            <td>${d.entrego ? '✔' : '✖'}</td>
            <td>${d.entrego ? '📖' : ''}</td>
            <td>${d.cal} <span onclick="editarCal('${nombre}')">✏️</span></td>
        </tr>`;
    });
}

/* EDITAR CAL */
function editarCal(nombre) {
    let nueva = prompt("Nueva calificación:");
    if (nueva) {
        entregas[nombre].cal = parseInt(nueva);
        renderTabla();
    }
}

/* MODAL */
function abrirModal() {
    editIndex = null;
    document.getElementById("modal").style.display = "flex";
}

function editarTarea(i) {
    editIndex = i;
    document.getElementById("modal").style.display = "flex";
}

function guardarTarea() {
    const titulo = document.getElementById("titulo").value;
    const materia = document.getElementById("materia").value;
    const grupo = document.getElementById("grupo").value;

    if (!titulo || !materia || !grupo) return;

    let nueva = { titulo, materia, grupo };

    if (editIndex !== null) tareas[editIndex] = nueva;
    else tareas.push(nueva);

    guardarLocal();
    render();
    cerrarModal();
}

function eliminarTarea(i) {
    tareas.splice(i, 1);
    guardarLocal();
    render();
}

function cerrarModal() {
    document.getElementById("modal").style.display = "none";
}

render();

function toggleMenu() {
    document.querySelector(".sidebar").classList.toggle("active");
}