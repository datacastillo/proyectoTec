/**
 * inscripcion.js - Gestión del proceso de inscripción de aspirantes
 */

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
        
        // TRAMPA PARA ERRORES DE PHP: Leemos como texto primero
        const textoCrudo = await response.text();
        let data;

        try {
            data = JSON.parse(textoCrudo);
        } catch (err) {
            console.error("🚨 ERROR REAL DE PHP (validar_inscripcion.php):\n", textoCrudo);
            alert("Error en el servidor. Abre la consola (F12) para ver los detalles.");
            return; // Detenemos la ejecución si no es JSON válido
        }

        if (data.success) {
            // Guardamos el folio de forma global para la función de finalizar
            folioActual = folio; 
            
            // Transición de Interfaz: Ocultar login, mostrar expediente
            document.getElementById('modal-validacion').style.display = 'none';
            document.getElementById('contenido-principal').style.display = 'block';
            
            // Llenar datos informativos del alumno
            document.getElementById('nombre-alumno').innerText = data.nombre;
            document.getElementById('carrera-alumno').innerText = data.carrera;

            // Renderizar las materias obtenidas de la BD (Sin créditos)
            const tbody = document.getElementById('lista-materias');
            
            if (data.materias && data.materias.length > 0) {
                tbody.innerHTML = data.materias.map(m => `
                    <tr>
                        <td><span class="status-badge">✅ CARGADA</span></td>
                        <td><strong>${m.clave}</strong></td>
                        <td>${m.nombre}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;">No hay materias registradas para esta carrera.</td></tr>`;
            }
            
        } else {
            // Error: Folio incorrecto, CURP mal escrita o estatus no 'aprobada'
            alert("Error de validación: " + data.message);
        }
    } catch (e) {
        console.error("Error en petición (Red):", e);
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
        // Llamada al archivo PHP que inserta en la tabla 'alumnos' y 'usuarios'
        const response = await fetch('confirmar_inscripcion.php', {
            method: 'POST',
            body: formData
        });
        
        // TRAMPA PARA ERRORES DE PHP: Leemos como texto primero
        const textoCrudo = await response.text();
        let resData;

        try {
            resData = JSON.parse(textoCrudo);
        } catch (err) {
            console.error("🚨 ERROR REAL DE PHP (confirmar_inscripcion.php):\n", textoCrudo);
            alert("Error al confirmar en el servidor. Abre la consola (F12) para ver los detalles.");
            return; // Detenemos la ejecución si no es JSON válido
        }

        if (resData.success) {
            // ÉXITO TOTAL: Extraemos datos de la respuesta para el alumno
            const nombreAlumno = document.getElementById('nombre-alumno').innerText;

            // Alerta informativa completa con las credenciales de acceso
            alert("¡INSCRIPCIÓN COMPLETADA EXITOSAMENTE! 🎉\n\n" +
                  "Aspirante: " + nombreAlumno + "\n" +
                  "--------------------------------------------------\n" +
                  "Número de Control (Matrícula): " + resData.num_control + "\n" +
                  "Correo Institucional: " + resData.correo + "\n" +
                  "--------------------------------------------------\n" +
                  "Contraseña de acceso: Tu CURP\n\n" +
                  "Bienvenido a la comunidad del Tec. Guarda bien tus datos.");
            
            // Redirigir al inicio del sitio
            window.location.href = "../index.html"; 
        } else {
            // Error devuelto por el servidor (ej. error de SQL)
            alert("Hubo un problema al procesar tu registro: " + resData.message);
        }
    } catch (error) {
        console.error("Error al finalizar (Red):", error);
        alert("Error crítico al intentar registrar la inscripción. Revisa tu conexión.");
    }
}