const MATERIAS = {
    CALCULO: {
        "6A": ["PANCHO", "JUANA", "TINA", "PEDRO", "PABLO"],
        "6B": ["LUIS", "MARIA", "CARLOS", "ANA", "SOFIA"]
    },
    ESPAÑOL: {
        "6A": ["MARIO", "LUCIA", "DANIEL", "FER", "ANDREA"],
        "6B": ["JORGE", "ELENA", "RAUL", "KAREN", "IVAN"]
    },
    HISTORIA: {
        "6A": ["DIEGO", "MATEO", "SANTIAGO", "BRUNO", "AXEL"],
        "6B": ["VALERIA", "PAOLA", "XIMENA", "FATIMA", "REBECA"]
    }
};

let materiaActual = "CALCULO";
let grupoActual = "6A";

let alumnoSeleccionado = "";
let unidadSeleccionada = 0;

let data = JSON.parse(localStorage.getItem("data")) || {};

function guardarLocal() {
    localStorage.setItem("data", JSON.stringify(data));
}

function key(nombre, unidad) {
    return `${nombre}_U${unidad}`;
}

function render() {
    const tbody = document.getElementById("tablaAlumnos");
    tbody.innerHTML = "";

    const alumnos = MATERIAS[materiaActual][grupoActual];

    alumnos.forEach(nombre => {
        let row = `<tr><td class="student-name">${nombre}</td>`;

        for (let i = 1; i <= 5; i++) {
            let val = data?.[materiaActual]?.[grupoActual]?.[key(nombre, i)] || "";

            row += `
            <td class="grade-cell">
                ${val}
                ${val >= 70 ? '<span class="icon-check">✔</span>' : ''}
                <span class="icon-edit" onclick="abrirModal('${nombre}', ${i})">✏</span>
            </td>`;
        }

        row += "</tr>";
        tbody.innerHTML += row;
    });

    document.querySelector(".header-impact").innerText = grupoActual;
}

function cambiarGrupo(grupo) {
    grupoActual = grupo;

    document.getElementById("btn6A").classList.remove("active");
    document.getElementById("btn6B").classList.remove("active");
    document.getElementById("btn" + grupo).classList.add("active");

    render();
}

function cambiarMateria(materia, elemento) {
    materiaActual = materia;

    document.querySelectorAll(".submenu li").forEach(li => li.classList.remove("sub-active"));
    elemento.classList.add("sub-active");

    render();
}

function abrirModal(nombre, unidad) {
    alumnoSeleccionado = nombre;
    unidadSeleccionada = unidad;

    let val = data?.[materiaActual]?.[grupoActual]?.[key(nombre, unidad)] || "";

    document.getElementById("calAnterior").value = val;
    document.getElementById("calNueva").value = "";

    document.getElementById("modalCalificacion").style.display = "flex";
}

function cerrarModal() {
    document.getElementById("modalCalificacion").style.display = "none";
    document.getElementById("calNueva").value = "";
}

function guardarCalificacion() {
    const nueva = document.getElementById("calNueva").value;
    if (!nueva) return;

    if (!data[materiaActual]) data[materiaActual] = {};
    if (!data[materiaActual][grupoActual]) data[materiaActual][grupoActual] = {};

    data[materiaActual][grupoActual][key(alumnoSeleccionado, unidadSeleccionada)] = parseInt(nueva);

    guardarLocal();
    render();
    cerrarModal();
}

function toggleSubmenu() {
    const sub = document.querySelector(".submenu");
    const arrow = document.querySelector(".arrow");

    if (sub.style.display === "none") {
        sub.style.display = "block";
        arrow.innerText = "▼";
    } else {
        sub.style.display = "none";
        arrow.innerText = "◀";
    }
}

document.addEventListener("DOMContentLoaded", render);

function toggleMenu() {
    document.querySelector(".sidebar").classList.toggle("active");
}