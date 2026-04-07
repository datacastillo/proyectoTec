let grupoActualId = 0;

// 1. Cargar alumnos desde la BD
function verAlumnos(grupoId, materiaNombre) {
    grupoActualId = grupoId;
    document.getElementById('vista_principal').style.display = 'none';
    document.getElementById('vista_alumnos').style.display = 'block';
    document.getElementById('titulo_materia').innerText = materiaNombre;

    // Petición AJAX al PHP
    fetch('get_alumnos.php?grupo_id=' + grupoId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('tabla_alumnos_res').innerHTML = html;
        });
}

// 2. Sistema de Alertas (Enlace a tabla notificaciones)
function enviarAlerta(usuarioId, nombre) {
    if (confirm(`¿Enviar alerta de bajo rendimiento a ${nombre}?`)) {
        fetch('enviar_alerta.php?id_usuario=' + usuarioId)
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "success") {
                    alert("✅ Notificación enviada al alumno.");
                } else {
                    alert("❌ Error: " + data);
                }
            });
    }
}

// 3. Regresar a la lista de materias
function regresar() {
    document.getElementById('vista_principal').style.display = 'block';
    document.getElementById('vista_alumnos').style.display = 'none';
    grupoActualId = 0;
}

// 4. Imprimir Lista (Usa el archivo que ya corregimos)
function imprimirListaAsistencia() {
    if(grupoActualId !== 0) {
        window.open('imprimir_lista.php?id_grupo=' + grupoActualId, '_blank');
    } else {
        alert("Selecciona un grupo primero.");
    }
}

// --- Gestión de Unidades ---
function abrirModalUnidad() { document.getElementById('modalUnidad').style.display = 'flex'; }
function cerrarModalUnidad() { 
    document.getElementById('modalUnidad').style.display = 'none';
    document.getElementById('formNuevaUnidad').reset();
}

// Función para enviar la notificación mediante AJAX
function enviarAlerta(idAlumnoUsuario, nombre) {
    if (confirm(`¿Deseas enviar una alerta formal a ${nombre} por bajo rendimiento?`)) {
        
        fetch(`enviar_alerta.php?id_usuario=${idAlumnoUsuario}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    alert("✅ La notificación ha sido enviada al alumno exitosamente.");
                } else {
                    alert("❌ Error al procesar la notificación: " + data);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("❌ Error de conexión con el servidor.");
            });
    }
}

// Función para imprimir (se mantiene igual pero asegura que use grupoActualId)
function imprimirListaAsistencia() {
    if(typeof grupoActualId !== 'undefined' && grupoActualId !== 0) {
        window.open('imprimir_lista.php?id_grupo=' + grupoActualId, '_blank');
    } else {
        alert("Por favor, selecciona un grupo primero.");
    }
}