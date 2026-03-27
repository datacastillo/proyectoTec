// VARIABLES GLOBALES
let actualEntregaId = null;

// 1. CARGAR LAS TAREAS (TARJETAS)
function cargarTareasMateria(materiaId) {
    fetch(`get_tareas_cards.php?materia_id=${materiaId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("cards").innerHTML = html;
        });
}

// 2. CARGAR LA TABLA DE ENTREGAS (EL "CUÁNDO" SE PULSA VER ENTREGAS)
function verEntregas(tareaId, tituloTarea) {
    // Cambiamos de vista (tu lógica de ocultar/mostrar)
    document.getElementById("cards").style.display = "none";
    const vistaTarea = document.getElementById("vistaTarea");
    vistaTarea.classList.remove("hidden");
    
    // Título de la tarea en la cabecera
    document.getElementById("tituloTareaSeleccionada").innerText = tituloTarea;

    // Traemos los datos reales del PHP que corregimos antes
    fetch(`get_entregas.php?tarea_id=${tareaId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("tablaTarea").innerHTML = html;
        });
}

// 3. FUNCIÓN PARA ABRIR EL VISOR (La que llama el botón "REVISAR")
function abrirVisor(entregaId, nombre, tarea, ruta, notaActual) {
    actualEntregaId = entregaId;
    
    // Llenar datos en el modal del visor
    document.getElementById('visor_nombre_alumno').innerText = nombre.toUpperCase();
    document.getElementById('visor_titulo_tarea').innerText = tarea;
    document.getElementById('visor_nota_input').value = (notaActual > 0) ? notaActual : "";
    
    const frame = document.getElementById('pdf_frame');
    const loading = document.getElementById('pdf_loading');
    
    frame.style.display = 'none';
    loading.style.display = 'block';
    
    // Cargamos el PDF real
    frame.src = ruta;
    frame.onload = function() {
        frame.style.display = 'block';
        loading.style.display = 'none';
    };

    document.getElementById('visor_modal').style.display = 'flex';
}

// 4. GUARDAR CALIFICACIÓN DESDE EL VISOR
function guardarCalificacionVisor() {
    const nota = document.getElementById('visor_nota_input').value;
    const btn = document.getElementById('btn_guardar_visor');

    if (nota === "" || nota < 0 || nota > 100) {
        alert("Ingresa una nota válida (0-100)");
        return;
    }

    btn.innerText = "⏳ Guardando...";
    btn.disabled = true;

    const data = new FormData();
    data.append('entrega_id', actualEntregaId);
    data.append('nota', nota);

    fetch('guardar_nota_tarea.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.text())
    .then(res => {
        if(res.trim() === "success") {
            // Cerramos y refrescamos la tabla para ver el cambio de color
            cerrarVisor();
            // Refrescamos la lista de entregas (asumiendo que tienes el id de tarea a mano)
            // Si no, un location.reload() o volver a llamar a verEntregas()
            alert("✅ Calificación guardada");
            location.reload(); 
        }
    })
    .catch(err => alert("Error al conectar con el servidor"));
}

// 5. FUNCIONES DE NAVEGACIÓN Y UI
function cerrarVisor() {
    document.getElementById('visor_modal').style.display = 'none';
    document.getElementById('pdf_frame').src = "";
}

function volver() {
    document.getElementById("cards").style.display = "flex";
    document.getElementById("vistaTarea").classList.add("hidden");
}

function toggleMenu() {
    document.querySelector(".sidebar").classList.toggle("active");
}

function cerrarModal() {
    document.getElementById("modal").style.display = "none";
}