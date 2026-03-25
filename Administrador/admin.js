let alumnos = JSON.parse(localStorage.getItem("alumnos")) || [];
let docentes = JSON.parse(localStorage.getItem("docentes")) || [];
let fichas = JSON.parse(localStorage.getItem("fichas")) || [
    { nombre: "Juan Perez", tipo: "Alumno", estado: "Pendiente" },
    { nombre: "Maria Lopez", tipo: "Docente", estado: "Pendiente" }
];

let tipoActual = "";
let editIndex = null;

function mostrarSeccion(seccion) {
    document.querySelectorAll(".seccion").forEach(s => s.style.display = "none");
    document.getElementById(seccion).style.display = "block";

    document.querySelectorAll(".nav-item").forEach(i => i.classList.remove("active"));
    event.currentTarget.classList.add("active");
}

function render() {

    // ALUMNOS
    let tablaA = document.getElementById("tablaAlumnos");
    if (tablaA) {
        tablaA.innerHTML = "";
        alumnos.forEach((a, i) => {
            tablaA.innerHTML += `
            <tr>
                <td>${a.id}</td>
                <td>${a.nombre}</td>
                <td>
                    <button onclick="editar('alumno', ${i})">✏️</button>
                    <button onclick="eliminar('alumno', ${i})">❌</button>
                </td>
            </tr>`;
        });
    }

    // DOCENTES
    let tablaD = document.getElementById("tablaDocentes");
    if (tablaD) {
        tablaD.innerHTML = "";
        docentes.forEach((d, i) => {
            tablaD.innerHTML += `
            <tr>
                <td>${d.id}</td>
                <td>${d.nombre}</td>
                <td>
                    <button onclick="editar('docente', ${i})">✏️</button>
                    <button onclick="eliminar('docente', ${i})">❌</button>
                </td>
            </tr>`;
        });
    }

    // FICHAS
    let tablaF = document.getElementById("tablaFichas");
    if (tablaF) {
        tablaF.innerHTML = "";
        fichas.forEach((f, i) => {
            tablaF.innerHTML += `
            <tr>
                <td>${f.nombre}</td>
                <td>${f.tipo}</td>
                <td>${f.estado}</td>
                <td>
                    <button onclick="aceptar(${i})">✔</button>
                    <button onclick="rechazar(${i})">❌</button>
                </td>
            </tr>`;
        });
    }
}

function abrirModal(tipo) {
    tipoActual = tipo;
    document.getElementById("userModal").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("userModal").style.display = "none";
    document.getElementById("userName").value = "";
    document.getElementById("userExtra").value = "";
}

document.getElementById("userForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const nombre = document.getElementById("userName").value;
    const extra = document.getElementById("userExtra").value;

    const nuevo = {
        id: Date.now(),
        nombre: nombre,
        extra: extra
    };

    if (tipoActual === "ALUMNO") {
        alumnos.push(nuevo);
        localStorage.setItem("alumnos", JSON.stringify(alumnos));
    } else {
        docentes.push(nuevo);
        localStorage.setItem("docentes", JSON.stringify(docentes));
    }

    cerrarModal();
    render();
});

function eliminar(tipo, index) {
    if (tipo === "alumno") {
        alumnos.splice(index, 1);
        localStorage.setItem("alumnos", JSON.stringify(alumnos));
    } else {
        docentes.splice(index, 1);
        localStorage.setItem("docentes", JSON.stringify(docentes));
    }
    render();
}

function editar(tipo, index) {
    let data = tipo === "alumno" ? alumnos[index] : docentes[index];

    document.getElementById("userName").value = data.nombre;
    document.getElementById("userExtra").value = data.extra;

    tipoActual = tipo === "alumno" ? "ALUMNO" : "DOCENTE";
    editIndex = index;

    abrirModal(tipoActual);
}

function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("active");
}

/* FICHAS */
function aceptar(index) {
    fichas[index].estado = "Aceptado";
    localStorage.setItem("fichas", JSON.stringify(fichas));
    render();
}

function rechazar(index) {
    fichas[index].estado = "Rechazado";
    localStorage.setItem("fichas", JSON.stringify(fichas));
    render();
}

render();