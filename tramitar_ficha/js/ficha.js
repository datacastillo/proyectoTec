function procesarRegistro() {
    const nom = document.getElementById('nombre').value.trim();
    const ape = document.getElementById('apellido').value.trim();
    const curp = document.getElementById('curp').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const carrSelect = document.getElementById('carrera');
    const carreraId = carrSelect.value;
    const carreraNombre = carrSelect.options[carrSelect.selectedIndex].text;

    // 2. Validaciones
    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const regexCurp = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;

    if(!nom || !ape || !curp || !correo || !carreraId) {
        alert("⚠️ Por favor, complete todos los campos obligatorios.");
        return;
    }

    if(curp.length !== 18) {
        alert("⚠️ La CURP debe tener exactamente 18 caracteres.");
        return;
    }

    if(!regexCorreo.test(correo)) {
        alert("⚠️ El correo electrónico no tiene un formato válido.");
        return;
    }

    // 3. Envío de datos al Servidor (PHP)
    const datos = new FormData();
    datos.append('nombre', nom);
    datos.append('apellido', ape);
    datos.append('curp', curp);
    datos.append('correo', correo);
    datos.append('carrera_id', carreraId);

    fetch('procesar_ficha.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.json())
    .then(result => {
        if(result.status === 'success') {
            // Llenado de datos en la ficha con el FOLIO REAL generado por PHP
            document.getElementById('display-folio').innerText = result.folio;
            document.getElementById('res-nombre').innerText = (nom + " " + ape).toUpperCase();
            document.getElementById('res-curp').innerText = curp.toUpperCase();
            document.getElementById('res-carrera').innerText = carreraNombre;
            document.getElementById('res-fecha').innerText = new Date().toLocaleDateString('es-MX', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });

            // Cambiar vista
            document.getElementById('form-ficha').style.display = 'none';
            document.getElementById('success-screen').style.display = 'block';
            window.scrollTo(0, 0);
        } else {
            alert("❌ Error en el servidor: " + result.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("❌ Hubo un fallo en la conexión con el servidor.");
    });
}