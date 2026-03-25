// Variable global para mantener el folio durante toda la sesión de inscripción
let folioActual = "";

/**
 * 1. VALIDA EL ACCESO
 * Se dispara cuando el aspirante ingresa Folio y CURP
 */
async function validarAcceso() {
    const folioInput = document.getElementById('folio-input');
    const curpInput = document.getElementById('curp-input');
    
    const folio = folioInput.value.trim();
    const curp = curpInput.value.trim();

    // Validar que los campos no estén vacíos
    if (!folio || !curp) {
        alert("Por favor, ingresa tu Folio y CURP para continuar.");
        return;
    }

    const formData = new FormData();
    formData.append('folio', folio);
    formData.append('curp', curp);

    try {
        // Petición al validador PHP
        const response = await fetch('validar_inscripcion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();

        if (data.success) {
            // Guardamos el folio de forma global para la función de finalizar
            folioActual = folio; 
            
            // Transición de Interfaz: Ocultar login, mostrar expediente
            document.getElementById('modal-validacion').style.display = 'none';
            document.getElementById('contenido-principal').style.display = 'block';
            
            // Llenar datos informativos del alumno
            document.getElementById('nombre-alumno').innerText = data.nombre;
            document.getElementById('carrera-alumno').innerText = data.carrera;

            // Renderizar las materias obtenidas de la BD
            const tbody = document.getElementById('lista-materias');
            tbody.innerHTML = data.materias.map(m => `
                <tr>
                    <td><span class="status-badge">✅ CARGADA</span></td>
                    <td><strong>${m.clave}</strong></td>
                    <td>${m.nombre}</td>
                    <td>${m.creditos} Cr.</td>
                </tr>
            `).join('');
            
        } else {
            // Error: Folio incorrecto, CURP mal escrita o estatus no 'aprobada'
            alert("Error de validación: " + data.message);
        }
    } catch (e) {
        console.error("Error en validación:", e);
        alert("No se pudo conectar con el servidor. Revisa tu conexión.");
    }
}

/**
 * 2. FINALIZA LA INSCRIPCIÓN
 * Se dispara al presionar el botón verde "CONFIRMAR E INSCRIBIR"
 */
async function finalizarInscripcion() {
    // Verificación de seguridad
    if (!folioActual) {
        alert("Error de sesión: No se encontró un folio válido.");
        return;
    }

    const confirmar = confirm("¿Deseas confirmar tu inscripción oficial?\n\nAl aceptar, se generará tu Número de Control y quedarás registrado como alumno.");
    
    if (!confirmar) return;

    // Preparamos los datos para enviar al procesador final
    const formData = new FormData();
    formData.append('folio', folioActual);

    try {
        // Llamada al archivo PHP que inserta en la tabla 'alumnos'
        const response = await fetch('confirmar_inscripcion.php', {
            method: 'POST',
            body: formData
        });
        
        const resData = await response.json();

        if (resData.success) {
            // Éxito Total
            alert("¡INSCRIPCIÓN COMPLETADA EXITOSAMENTE!\n\n" +
                  "Aspirante: " + document.getElementById('nombre-alumno').innerText + "\n" +
                  "Número de Control Oficial: " + resData.num_control + "\n\n" +
                  "Bienvenido a la comunidad del Tec. Guarda bien tu número.");
            
            // Redirigir al inicio o login de alumnos
            window.location.href = "../index.html"; 
        } else {
            // Error devuelto por el servidor (ej. error de SQL)
            alert("Hubo un problema al procesar tu registro: " + resData.message);
        }
    } catch (error) {
        console.error("Error al finalizar:", error);
        alert("Error crítico al intentar registrar la inscripción.");
    }
}