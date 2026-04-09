/**
 * admin.js - Gestión del Panel de Administración
 */

let alumnos = [];
let docentes = [];
let fichas = []; 
let materias = []; 
let tipoActual = "";

// --- INTEGRACIÓN DE AVATAR (CORREGIDO A ASSETS) ---
let avatarSeleccionado = "default.png";
const rutaAvatares = "assets/avatares/"; 

// 1. CARGAR DATOS DESDE LA BD (API)
async function cargarDatosBD(tipo) {
    try {
        let url = "";
        if (tipo === 'ficha') {
            url = 'api/obtener_fichas.php';
        } else if (tipo === 'materia') {
            url = 'api/obtener_materias.php'; 
        } else {
            url = `api/obtener_usuarios.php?tipo=${tipo}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.error) {
            console.error("Error del servidor:", data.error);
            return;
        }

        if (tipo === 'alumno') {
            alumnos = data;
        } else if (tipo === 'docente') {
            docentes = data;
        } else if (tipo === 'ficha') {
            fichas = data; 
        } else if (tipo === 'materia') {
            materias = data; 
        }
        render(); 
    } catch (error) {
        console.error("Error cargando la base de datos:", error);
    }
}

// 2. CAMBIAR ENTRE SECCIONES
function mostrarSeccion(seccion) {
    document.querySelectorAll(".seccion").forEach(s => s.style.display = "none");
    const target = document.getElementById(seccion);
    if(target) target.style.display = "block";

    if (window.event && window.event.currentTarget) {
        document.querySelectorAll(".nav-item").forEach(i => i.classList.remove("active"));
        window.event.currentTarget.classList.add("active");
    }
    
    if(seccion === 'alumnos') cargarDatosBD('alumno');
    if(seccion === 'docentes') cargarDatosBD('docente');
    if(seccion === 'fichas') cargarDatosBD('ficha');
    if(seccion === 'materias') cargarDatosBD('materia'); 
    
    if(seccion === 'carga') {
        Promise.all([cargarDatosBD('docente'), cargarDatosBD('materia')]).then(() => {
            cargarCargaAcademica();
        });
    }
}

// 3. RENDERIZAR TABLAS HTML (OPTIMIZADO PARA MAYOR RENDIMIENTO)
function render() {
    let tablaA = document.getElementById("tablaAlumnos");
    if (tablaA) {
        let htmlAlumnos = ""; // Se almacena en string para no recargar el DOM en cada iteración
        alumnos.forEach((a) => {
            htmlAlumnos += `
            <tr>
                <td>${a.id}</td>
                <td>
                    <img src="${rutaAvatares}${a.avatar || 'default.png'}" style="width:30px; height:30px; border-radius:50%; margin-right:10px; vertical-align:middle; object-fit:cover;">
                    ${a.nombre_completo || a.nombre}
                </td>
                <td>${a.extra || a.correo || 'Sin Matrícula'}</td>
                <td>
                    <button class="btn-primary" style="background:#3e92cc; margin-right:5px;" onclick="editar('alumno', '${a.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="eliminar('alumno', '${a.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        tablaA.innerHTML = htmlAlumnos; // Se inyecta una sola vez
    }

    let tablaD = document.getElementById("tablaDocentes");
    if (tablaD) {
        let htmlDocentes = "";
        docentes.forEach((d) => {
            htmlDocentes += `
            <tr>
                <td>${d.id}</td>
                <td>
                    <img src="${rutaAvatares}${d.avatar || 'default.png'}" style="width:30px; height:30px; border-radius:50%; margin-right:10px; vertical-align:middle; object-fit:cover;">
                    ${d.nombre_completo || d.nombre}
                </td>
                <td>${d.extra || 'Sin Especialidad'}</td>
                <td>
                    <button class="btn-primary" style="background:#3e92cc; margin-right:5px;" onclick="editar('docente', '${d.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="eliminar('docente', '${d.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        tablaD.innerHTML = htmlDocentes;
    }

    let tablaF = document.getElementById("tablaFichas");
    if (tablaF) {
        let htmlFichas = "";
        fichas.forEach((f) => {
            let colorBg = f.estatus === 'pendiente' ? '#f39c12' : 
                         (f.estatus === 'aprobada' ? '#2ecc71' : 
                         (f.estatus === 'rechazada' ? '#e74c3c' : 
                         (f.estatus === 'inscrito' ? '#3498db' : '#34495e')));

            htmlFichas += `
            <tr>
                <td>${f.nombre_completo}<br><small style="color:gray;">Folio: ${f.folio}</small></td>
                <td>Carrera ${f.carrera_id}</td>
                <td><span style="background:${colorBg}; color:white; padding:3px 8px; border-radius:4px; font-size:12px;">${f.estatus.toUpperCase()}</span></td>
                <td>
                    <button class="btn-primary" style="background:#2ecc71; margin-right:5px;" onclick="cambiarEstadoFicha(${f.id}, 'aprobada')" title="Aprobar Ficha"><i class="fas fa-check"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="cambiarEstadoFicha(${f.id}, 'rechazada')" title="Rechazar Ficha"><i class="fas fa-times"></i></button>
                </td>
            </tr>`;
        });
        tablaF.innerHTML = htmlFichas;
    }

    let tablaM = document.getElementById("tablaMaterias");
    if (tablaM) {
        let htmlMaterias = "";
        materias.forEach((m) => {
            htmlMaterias += `
            <tr>
                <td>${m.clave}</td>
                <td>${m.nombre}</td>
                <td>Carrera ${m.carrera_id}</td>
                <td>Sem. ${m.semestre}</td>
                <td>
                    <button class="btn-primary" style="background:#3e92cc; margin-right:5px;" onclick="editar('materia', '${m.id}')"><i class="fas fa-edit"></i></button>
                    <button class="btn-primary" style="background:#e74c3c;" onclick="eliminar('materia', '${m.id}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        tablaM.innerHTML = htmlMaterias;
    }
}

// 4. GESTIÓN DEL MODAL

// 4. GESTIÓN DEL MODAL
function abrirModal(tipo) {
    tipoActual = tipo; 
    const modal = document.getElementById("userModal");
    const emailGroup = document.getElementById("emailGroup");
    const passGroup = document.getElementById("passGroup");
    const avatarGroup = document.getElementById("avatarGroup"); 
    const inputNombre = document.getElementById("userName");

    // 🔍 Búsqueda forzada del Label (buscamos por su texto para no depender del HTML)
    let labelNombre = null;
    if (modal) {
        const labels = modal.getElementsByTagName("label");
        for (let l of labels) {
            if (l.innerText.includes("Nombre Completo") || l.innerText.includes("Nombre de la Materia")) {
                labelNombre = l;
                break;
            }
        }
    }

    if (modal) {
        modal.style.display = "flex"; 
        
        if (tipo === 'MATERIA') {
            document.getElementById("modalTitle").innerText = 'Nueva Materia';
            document.getElementById("extraLabel").innerText = 'Clave';
            
            // 🏷️ Ajustamos textos para Materia
            if(labelNombre) labelNombre.innerText = 'Nombre de la Materia';
            if(inputNombre) inputNombre.placeholder = 'Ej. Cálculo Diferencial';

            if(emailGroup) emailGroup.style.display = "none";
            if(passGroup) passGroup.style.display = "none";
            if(avatarGroup) avatarGroup.style.display = "none";
            
            // 🩺 Quitamos la obligación de TODOS los campos ocultos (Evita el error en consola)
            if(document.getElementById("userEmail")) document.getElementById("userEmail").required = false;
            if(document.getElementById("userPass")) document.getElementById("userPass").required = false;
            if(document.getElementById("userAvatar")) document.getElementById("userAvatar").required = false;

        } else {
            document.getElementById("modalTitle").innerText = tipo === 'ALUMNO' ? 'Nuevo Alumno' : 'Nuevo Docente';
            document.getElementById("extraLabel").innerText = tipo === 'ALUMNO' ? 'Matrícula' : 'Especialidad';
            
            // 🏷️ Restauramos textos para Usuarios
            if(labelNombre) labelNombre.innerText = 'Nombre Completo';
            if(inputNombre) inputNombre.placeholder = 'Ej. Juan Castillo';

            if(emailGroup) emailGroup.style.display = "block";
            if(passGroup) passGroup.style.display = "block";
            if(avatarGroup) avatarGroup.style.display = "block";

            // 🩺 Devolvemos la obligación de llenarlos
            if(document.getElementById("userEmail")) document.getElementById("userEmail").required = true;
            if(document.getElementById("userPass")) document.getElementById("userPass").required = true;
        }
        
        // Limpiamos los campos al abrir
        document.getElementById("userId").value = ""; 
        if(inputNombre) inputNombre.value = "";
        if(document.getElementById("userEmail")) document.getElementById("userEmail").value = "";
        if(document.getElementById("userPass")) document.getElementById("userPass").value = "";
        if(document.getElementById("userExtra")) document.getElementById("userExtra").value = "";
        
        seleccionarAvatar('default.png');
    }
}

// --- FUNCIÓN PARA SELECCIONAR AVATAR ---
function seleccionarAvatar(archivo) {
    avatarSeleccionado = archivo;
    
    const inputAvatar = document.getElementById('userAvatar');
    if(inputAvatar) inputAvatar.value = archivo;

    document.querySelectorAll('.avatar-option, .avatar-opcion').forEach(img => {
        img.classList.remove('selected');
        if(img.src.includes(archivo)) {
            img.classList.add('selected');
        }
    });
}

function cerrarModal() {
    document.getElementById("userModal").style.display = "none";
}

// 5. GUARDAR USUARIO / MATERIA
async function guardarUsuario() {
    const id = document.getElementById("userId").value;
    const nombre = document.getElementById("userName").value;
    const correo = document.getElementById("userEmail").value;
    const password = document.getElementById("userPass").value;
    const extra = document.getElementById("userExtra").value;

    if(!nombre || (tipoActual !== 'MATERIA' && !correo)) {
        alert("Por favor rellena los campos obligatorios.");
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('nombre', nombre);
    formData.append('correo', correo);
    formData.append('password', password);
    formData.append('rol', tipoActual.toLowerCase());
    formData.append('extra', extra);
    formData.append('avatar', avatarSeleccionado);

    try {
        const response = await fetch('api/guardar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const res = await response.json();

        if(res.success) {
            alert("¡Registro procesado correctamente!");
            cerrarModal();
            cargarDatosBD(tipoActual.toLowerCase());
        } else {
            alert("Error: " + res.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

// 6. UTILIDADES
function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("active");
}

// 7. FUNCIONES DE ACCIÓN
function editar(tipo, id) {
    tipoActual = tipo.toUpperCase(); 
    let persona;
    
    if (tipo === 'alumno') persona = alumnos.find(a => a.id == id);
    else if (tipo === 'docente') persona = docentes.find(d => d.id == id);
    else if (tipo === 'materia') persona = materias.find(m => m.id == id);

    if (persona) {
        abrirModal(tipoActual);
        document.getElementById("userId").value = persona.id; 
        document.getElementById("userName").value = persona.nombre_completo || persona.nombre;
        document.getElementById("userEmail").value = persona.correo || ""; 
        document.getElementById("userExtra").value = persona.extra || persona.clave || "";
        
        if(persona.avatar) seleccionarAvatar(persona.avatar);
    }
}

async function eliminar(tipo, id) {
    if(confirm(`¿Estás seguro de eliminar este ${tipo}? Esta acción no se puede deshacer.`)) {
        try {
            const response = await fetch(`api/eliminar_usuario.php?id=${id}&tipo=${tipo}`);
            
            if (!response.ok) {
                throw new Error("El servidor no respondió correctamente.");
            }

            const res = await response.json();
            
            if (res.success) {
                alert("✅ Eliminado con éxito.");
                cargarDatosBD(tipo.toLowerCase());
            } else {
                alert("❌ No se pudo eliminar: " + (res.message || "Error desconocido en la base de datos."));
            }
        } catch (error) {
            console.error("Error crítico:", error);
            alert("⚠️ Hubo un error de conexión o el archivo PHP tiene un fallo. Revisa la consola.");
        }
    }
}

async function cambiarEstadoFicha(id, nuevoEstado) {
    if(confirm(`¿Estás seguro de marcar esta ficha como ${nuevoEstado.toUpperCase()}?`)) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('estatus', nuevoEstado);

            const response = await fetch('api/cambiar_estado_ficha.php', {
                method: 'POST',
                body: formData
            });
            
            const res = await response.json();

            if (res.success) {
                alert(res.message || "Estado actualizado correctamente.");
                cargarDatosBD('ficha');
                cargarDatosBD('alumno'); 
            } else {
                alert("Error: " + res.message);
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }
}

window.onload = () => {
    cargarDatosBD('alumno');
};

// ======================================================================
// --- FUNCIONES PARA DOCENTES Y CARGA ACADÉMICA ---
// ======================================================================

function llenarSelectsCarga() {
    const selectDocente = document.getElementById("selectDocenteCarga");
    if(selectDocente) {
        selectDocente.innerHTML = '<option value="">Seleccione un docente...</option>';
        docentes.forEach(d => {
            selectDocente.innerHTML += `<option value="${d.docente_id}">${d.nombre_completo || d.nombre}</option>`;
        });
    }
}

function cargarMateriasPorSemestre(semestre) {
    const selectMateria = document.getElementById("selectMateriaCarga");
    if(!selectMateria) return;

    if(!semestre) {
        selectMateria.innerHTML = '<option value="">Seleccione un semestre primero...</option>';
        return;
    }

    const filtradas = materias.filter(m => m.semestre == semestre);

    selectMateria.innerHTML = '<option value="">Seleccione una materia...</option>';
    filtradas.forEach(m => {
        selectMateria.innerHTML += `<option value="${m.id}">${m.nombre} (${m.clave})</option>`;
    });

    if(filtradas.length === 0) {
        selectMateria.innerHTML = '<option value="">No hay materias en este semestre</option>';
    }
}

async function cargarCargaAcademica() {
    try {
        const response = await fetch('api/obtener_carga.php');
        const data = await response.json();
        
        let tablaC = document.getElementById("tablaCargaAcademica");
        if (tablaC) {
            if(data.error || data.length === 0) {
                tablaC.innerHTML = "<tr><td colspan='6' style='text-align:center;'>No hay materias asignadas aún</td></tr>";
                llenarSelectsCarga();
                return;
            }
            
            let htmlCarga = ""; // Optimización de string
            data.forEach(c => {
                htmlCarga += `
                <tr>
                    <td>${c.docente_nombre}</td>
                    <td>${c.materia_nombre}</td>
                    <td>${c.nombre_grupo}</td>
                    <td>Sem. ${c.semestre}</td>
                    <td>${c.ciclo_escolar}</td>
                    <td>
                        <button class="btn-primary" style="background:#e74c3c;" onclick="eliminarCarga('${c.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            tablaC.innerHTML = htmlCarga;
        }
        llenarSelectsCarga(); 
    } catch (error) {
        console.error("Error cargando carga académica:", error);
    }
}

async function eliminarCarga(grupo_id) {
    if(confirm("¿Estás seguro de eliminar este grupo asignado?")) {
        try {
            const response = await fetch(`api/eliminar_carga.php?id=${grupo_id}`);
            const res = await response.json();
            if(res.success) {
                alert("Grupo eliminado con éxito.");
                cargarCargaAcademica();
            } else {
                alert("Error: " + res.message);
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }
}

// LISTENERS PARA MODALES DE REGISTRO
document.addEventListener("DOMContentLoaded", () => {
    
    const formRegistrarAlumno = document.getElementById('formRegistrarAlumno');
    if (formRegistrarAlumno) {
        formRegistrarAlumno.addEventListener('submit', async function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('rol', 'alumno'); 

            try {
                const response = await fetch('api/registrar_alumno.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.success) {
                    alert('✅ ' + res.message);
                    formRegistrarAlumno.reset();
                    document.getElementById('modalRegistrarAlumno').style.display = 'none';
                    cargarDatosBD('alumno'); 
                } else {
                    alert('❌ Error: ' + res.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    const formRegistrarDocente = document.getElementById('formRegistrarDocente');
    if (formRegistrarDocente) {
        formRegistrarDocente.addEventListener('submit', async function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            try {
                const response = await fetch('api/registrar_docente.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.success) {
                    alert('✅ ' + res.message);
                    formRegistrarDocente.reset();
                    document.getElementById('modalRegistrarDocente').style.display = 'none';
                    cargarDatosBD('docente'); 
                } else {
                    alert('❌ Error: ' + res.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    const formAsignarCarga = document.getElementById('formAsignarCarga');
    if (formAsignarCarga) {
        formAsignarCarga.addEventListener('submit', async function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            try {
                const response = await fetch('api/asignar_carga.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.success) {
                    alert('✅ ' + res.message);
                    formAsignarCarga.reset();
                    document.getElementById('modalAsignarCarga').style.display = 'none';
                    cargarCargaAcademica(); 
                } else {
                    alert('❌ Error: ' + res.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
});

// ======================================================================
// --- BUSCADORES EN TIEMPO REAL (OPTIMIZADO) ---
// ======================================================================

// Función genérica para evitar repetir la misma lógica de búsqueda
function configurarBuscador(idInput, idTabla) {
    const input = document.getElementById(idInput);
    if (input) {
        input.addEventListener('keyup', function() {
            let textoBusqueda = this.value.toLowerCase();
            let filas = document.querySelectorAll(`#${idTabla} tr`); 
            filas.forEach(function(fila) { 
                let contenidoFila = fila.textContent.toLowerCase();
                fila.style.display = contenidoFila.includes(textoBusqueda) ? '' : 'none';
            });
        });
    }
}

// Inicializamos los 3 buscadores de forma limpia
document.addEventListener('DOMContentLoaded', function() {
    configurarBuscador('buscadorAlumnos', 'tablaAlumnos');
    configurarBuscador('buscadorDocentes', 'tablaDocentes');
    configurarBuscador('buscadorMaterias', 'tablaMaterias');
});

// ======================================================================
// --- ACTUALIZAR PERFIL DEL ADMINISTRADOR ---
// ======================================================================
async function actualizarPerfil(event) {
    event.preventDefault(); // Evita definitivamente que la página se recargue

    const form = document.getElementById('formMiPerfil');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('api/actualizar_perfil.php', {
            method: 'POST',
            body: formData
        });

        const res = await response.json();

        if (res.success) {
            alert('✅ Perfil actualizado correctamente');
            
            // ACTUALIZACIÓN VISUAL INMEDIATA (Sin recargar)
            const nuevoAvatar = document.getElementById('inputAvatarSeleccionado').value;
            const rutaCompleta = 'assets/avatares/' + nuevoAvatar;

            if(document.getElementById('imgSideBar')) {
                document.getElementById('imgSideBar').src = rutaCompleta;
            }
            if(document.getElementById('imgPerfilGrande')) {
                document.getElementById('imgPerfilGrande').src = rutaCompleta;
            }

        } else {
            alert('❌ Error al actualizar: ' + res.message);
        }
    } catch (error) {
        console.error('Error en la petición:', error);
        alert('⚠️ Hubo un error de conexión con el servidor.');
    }
}