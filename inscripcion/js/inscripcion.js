// Variable global para guardar el folio actual
let folioActual = "";

async function validarAcceso() {
    const folio = document.getElementById('folio-input').value;
    const curp = document.getElementById('curp-input').value;

    // Validar que los campos no estén vacíos antes de enviar
    if (!folio || !curp) {
        alert("Por favor, ingresa tu Folio y CURP.");
        return;
    }

    const formData = new FormData();
    formData.append('folio', folio);
    formData.append('curp', curp);

    try {
        const response = await fetch('validar_inscripcion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();

        if (data.success) {
            folioActual = folio; // Guardamos el folio para uso posterior
            
            // Efecto visual: Ocultar acceso y mostrar expediente
            document.getElementById('modal-validacion').style.display = 'none';
            document.getElementById('contenido-principal').style.display = 'block';
            
            // Llenar datos del encabezado
            document.getElementById('nombre-alumno').innerText = data.nombre;
            document.getElementById('carrera-alumno').innerText = data.carrera;

            // Llenar la tabla con el nuevo diseño de etiquetas (Badges)
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
            // Si los datos son incorrectos o el estatus no es 'aprobada'
            alert(data.message);
        }
    } catch (e) {
        console.error("Error:", e);
        alert("Error crítico: No se pudo conectar con el servidor de validación.");
    }
}

/**
 * Función que se dispara al confirmar la inscripción
 */
function finalizarInscripcion() {
    // Generamos un número de control ficticio (Ej: 2612 + 4 dígitos)
    const numControl = "2612" + Math.floor(1000 + Math.random() * 9000);
    
    alert("¡INSCRIPCIÓN COMPLETADA EXITOSAMENTE!\n\n" +
          "Aspirante: " + document.getElementById('nombre-alumno').innerText + "\n" +
          "Número de Control: " + numControl + "\n\n" +
          "Bienvenido a la comunidad del Tec.");
          
    // Redirigir al inicio después de aceptar
    window.location.href = "../index.html"; 
}