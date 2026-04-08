let grupoActualId = 0;

// 1. Cargar alumnos desde la BD
window.verAlumnos = function(grupoId, materiaNombre) {
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
};

// 2. Sistema de Alertas (Garantizado como global para evitar el ReferenceError)
window.enviarAlerta = function(usuarioId, nombre) {
    // Obtenemos el nombre de la materia del título para que la alerta sea específica
    const nombreMateria = document.getElementById('titulo_materia').innerText;

    if (confirm(`¿Enviar alerta de bajo rendimiento a ${nombre} en la materia ${nombreMateria}?`)) {
        
        // Enviamos el id_usuario y la materia
        fetch(`enviar_alerta.php?id_usuario=${usuarioId}&materia=${encodeURIComponent(nombreMateria)}`)
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "success") {
                    alert("✅ Notificación enviada al alumno.");
                } else {
                    alert("❌ Error: " + data);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("❌ Error de conexión con el servidor.");
            });
    }
};

// 3. Regresar a la lista de materias
window.regresar = function() {
    document.getElementById('vista_principal').style.display = 'block';
    document.getElementById('vista_alumnos').style.display = 'none';
    grupoActualId = 0;
};

// 4. Imprimir Lista de Asistencia
window.imprimirListaAsistencia = function() {
    if(typeof grupoActualId !== 'undefined' && grupoActualId !== 0) {
        window.open('imprimir_lista.php?id_grupo=' + grupoActualId, '_blank');
    } else {
        alert("⚠️ Por favor, selecciona un grupo primero.");
    }
};

// 5. Gestión de Unidades (Modales)
window.abrirModalUnidad = function() { 
    document.getElementById('modalUnidad').style.display = 'flex'; 
};

window.cerrarModalUnidad = function() { 
    document.getElementById('modalUnidad').style.display = 'none';
    const form = document.getElementById('formNuevaUnidad');
    if(form) form.reset();
};