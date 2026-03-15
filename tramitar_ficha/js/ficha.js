function procesarRegistro() {
    // 1. Obtención de valores
    const nom = document.getElementById('nombre').value.trim();
    const ape = document.getElementById('apellido').value.trim();
    const curp = document.getElementById('curp').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const carr = document.getElementById('carrera');

    // 2. Validaciones)
    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    // La CURP debe tener 10 letras iniciales, luego 6 números, luego 2 caracteres finales (total 18)
    const regexCurp = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;

    // 3. Verificación de campos vacíos
    if(!nom || !ape || !curp || !correo || !carr.value) {
        alert(" Por favor, complete todos los campos obligatorios.");
        return;
    }

    // 4. Validación de CURP (Longitud y Formato)
    if(curp.length !== 18) {
        alert(" La CURP debe tener exactamente 18 caracteres.");
        return;
    }
    
  
    // 5. Validación de Correo Electrónico
    if(!regexCorreo.test(correo)) {
        alert(" El correo electrónico no tiene un formato válido (ejemplo@correo.com).");
        return;
    }

    // 6. Si todo está bien, generar la ficha
    console.log("Registro exitoso para:", nom);
    
    // Folio solicitado
    document.getElementById('display-folio').innerText = "TEC-2026-001";
    
    // Llenado de datos en la ficha de impresión
    document.getElementById('res-nombre').innerText = (nom + " " + ape).toUpperCase();
    document.getElementById('res-curp').innerText = curp.toUpperCase();
    document.getElementById('res-carrera').innerText = carr.options[carr.selectedIndex].text;
    document.getElementById('res-fecha').innerText = new Date().toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    // Cambiar vista: Ocultar formulario y mostrar éxito/impresión
    document.getElementById('form-ficha').style.display = 'none';
    document.getElementById('success-screen').style.display = 'block';
    
    // Subir el scroll al inicio para que vean la ficha
    window.scrollTo(0, 0);
}