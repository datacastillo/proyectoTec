/**
 * admin.js - Gestión del Panel de Administración
 */

let alumnos = [];
let docentes = [];
let fichas = []; 
let tipoActual = "";

// 1. CARGAR DATOS DESDE LA BD (API)
async function cargarDatosBD(tipo) {
    try {
        const response = await fetch(`api/obtener_usuarios.php?tipo=${tipo}`);
        const data = await response.json();
        
        if (data.error) {
            console.error("Error del servidor:", data.error);
            return;
        }

        if (tipo === 'alumno') {
            alumnos = data;
        } else {
            docentes = data;
        }
        render(); // Dibujar la tabla correspondiente
    } catch (error) {
        console.error("Error cargando la base de datos:", error);
    }
}

// 2. CAMBIAR ENTRE SECCIONES (ALUMNOS / DOCENTES / FICHAS)
function mostrarSeccion(seccion) {
    // Ocultar todas las secciones
    document.querySelectorAll(".seccion").forEach(s => s.style.display = "none");
    // Mostrar la seleccionada
    document.getElementById(seccion).style.display = "block";

    // Actualizar estado activo en el menú lateral
    if (event && event.currentTarget) {
        document.querySelectorAll(".nav-item").forEach(i => i.classList.remove("active"));
        event.currentTarget.classList.add("active");
    }
    
    // Cargar datos automáticamente al cambiar de pestaña
    if(seccion === 'alumnos') cargarDatosBD('alumno');
    if(seccion === 'docentes') cargarDatosBD('docente');
}

// 3. RENDERIZAR TABLAS HTML
function render() {
    // Tabla Alumnos
    let tablaA = document.getElementById("tablaAlumnos");
    if (tablaA) {
        tablaA.innerHTML = "";
        alumnos.forEach((a) => {
            tablaA.innerHTML += `
            <tr>
                <td>${a.id}</td>
                <td>${a.nombre}</td>
                <td>${a.extra || 'Sin Matrícula'}</td>
                <td>
                    <button class="btn-primary" style="background:#3e92cc; margin-right:5px;" onclick="editar('alumno', ${a.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="eliminar('alumno', ${a.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }

    // Tabla Docentes
    let tablaD = document.getElementById("tablaDocentes");
    if (tablaD) {
        tablaD.innerHTML = "";
        docentes.forEach((d) => {
            tablaD.innerHTML += `
            <tr>
                <td>${d.id}</td>
                <td>${d.nombre}</td>
                <td>${d.extra || 'Sin Especialidad'}</td>
                <td>
                    <button class="btn-primary" style="background:#3e92cc; margin-right:5px;" onclick="editar('docente', ${d.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="eliminar('docente', ${d.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
}

// 4. GESTIÓN DEL MODAL (ABRIR / CERRAR)
function abrirModal(tipo) {
    tipoActual = tipo; // Guardamos si es ALUMNO o DOCENTE
    const modal = document.getElementById("userModal");
    
    if (modal) {
        modal.style.display = "flex"; // Mostrar el modal
        
        // Ajustar textos dinámicamente
        document.getElementById("modalTitle").innerText = tipo === 'ALUMNO' ? 'Nuevo Alumno' : 'Nuevo Docente';
        document.getElementById("extraLabel").innerText = tipo === 'ALUMNO' ? 'Matrícula' : 'Especialidad';
        
        // Limpiar campos para un nuevo registro
        document.getElementById("userId").value = ""; // IMPLEMENTADO: Se limpia el ID oculto
        document.getElementById("userName").value = "";
        document.getElementById("userEmail").value = "";
        document.getElementById("userPass").value = "";
        document.getElementById("userExtra").value = "";
        
        // Contraseña obligatoria para nuevos
        document.getElementById("userPass").required = true;
        document.getElementById("userPass").placeholder = "********";

        // Asegurar que los campos no estén bloqueados
        document.getElementById("userExtra").disabled = false;
    } else {
        console.error("No se encontró el modal con ID userModal");
    }
}

function cerrarModal() {
    document.getElementById("userModal").style.display = "none";
}

// 5. GUARDAR USUARIO EN LA BD (ENVÍO A PHP)
async function guardarUsuario() {
    const id = document.getElementById("userId").value; // ¡Aquí tomamos el ID oculto!
    const nombre = document.getElementById("userName").value;
    const correo = document.getElementById("userEmail").value;
    const password = document.getElementById("userPass").value;
    const extra = document.getElementById("userExtra").value;

    // Validación según si es nuevo o edición
    if(!nombre || !correo || !extra) {
        alert("Por favor rellena todos los campos obligatorios.");
        return;
    }
    if(!id && !password) { // Si NO hay ID (es nuevo) y NO hay contraseña
        alert("La contraseña es obligatoria para crear un nuevo usuario.");
        return;
    }

    // Preparar datos para el envío
    const formData = new FormData();
    formData.append('id', id); // Enviamos el ID al PHP
    formData.append('nombre', nombre);
    formData.append('correo', correo);
    formData.append('password', password);
    formData.append('rol', tipoActual.toLowerCase());
    formData.append('extra', extra);

    try {
        const response = await fetch('api/guardar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();

        if(res.success) {
            alert(id ? "¡Registro actualizado correctamente!" : "¡Registro guardado correctamente!");
            cerrarModal();
            // Recargar la tabla donde estábamos
            cargarDatosBD(tipoActual.toLowerCase());
        } else {
            alert("Error al guardar: " + res.message);
        }
    } catch (error) {
        console.error("Error en la petición:", error);
        alert("Hubo un error al conectar con el servidor.");
    }
}

// 6. UTILIDADES
function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("active");
}

// 7. FUNCIONES DE ACCIÓN
// IMPLEMENTADO: Lógica real para editar respetando tus arrays globales
function editar(tipo, id) {
    tipoActual = tipo.toUpperCase(); 
    
    // Buscamos a la persona en el arreglo correspondiente
    let persona = tipo === 'alumno' ? alumnos.find(a => a.id == id) : docentes.find(d => d.id == id);

    if (persona) {
        const modal = document.getElementById("userModal");
        modal.style.display = "flex";

        document.getElementById("modalTitle").innerText = tipo === 'alumno' ? 'Editar Alumno' : 'Editar Docente';
        document.getElementById("extraLabel").innerText = tipo === 'alumno' ? 'Matrícula' : 'Especialidad';

        // Llenamos el formulario
        document.getElementById("userId").value = persona.id; 
        document.getElementById("userName").value = persona.nombre;
        document.getElementById("userEmail").value = persona.correo || ""; 
        document.getElementById("userExtra").value = persona.extra || "";

        // Ajustes de contraseña para edición
        document.getElementById("userPass").value = "";
        document.getElementById("userPass").required = false;
        document.getElementById("userPass").placeholder = "Dejar en blanco para no cambiarla";
    } else {
        alert("No se encontraron los datos para editar.");
    }
}

async function eliminar(tipo, id) {
    // Confirmación antes de borrar
    if(confirm(`¿Estás seguro de eliminar este ${tipo}? Esta acción borrará sus registros asociados y no se puede deshacer.`)) {
        try {
            // Llamada al API con parámetros GET
            const response = await fetch(`api/eliminar_usuario.php?id=${id}&tipo=${tipo}`);
            const res = await response.json();

            if (res.success) {
                alert("Usuario eliminado con éxito.");
                // Recargar la tabla actual forzando la actualización de la lista
                cargarDatosBD(tipo.toLowerCase());
            } else {
                // Mostrar el error detallado si el PHP devuelve success: false
                alert("Error al eliminar: " + res.message);
            }
        } catch (error) {
            console.error("Error al eliminar:", error);
            alert("Hubo un error al procesar la eliminación en el servidor.");
        }
    }
}

// INICIO AUTOMÁTICO
window.onload = () => {
    cargarDatosBD('alumno'); // Carga inicial por defecto
};