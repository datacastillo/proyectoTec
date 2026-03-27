<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
// Obtener el ID del docente vinculado al usuario
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// Obtener grupos del docente para el selector
$query = "SELECT g.id, g.nombre_grupo, m.nombre AS materia 
          FROM grupos g 
          JOIN materias m ON g.materia_id = m.id 
          WHERE g.docente_id = '$id_docente'";
$grupos = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones Finales | ISIC</title>
    <style>
        :root {
            --bg-principal: #0f3145;
            --bg-secundario: #255b68;
            --accent: #2b6671;
            --texto: #ffffff;
            --subtexto: #adb5bd;
            --exito: #2ecc71;
        }

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-principal);
            color: var(--texto);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* BARRA LATERAL FIJA */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background-color: var(--bg-secundario);
            border-right: 1px solid var(--accent);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: var(--subtexto);
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: var(--bg-principal);
            color: white;
            border-left: 4px solid var(--exito);
        }

        /* CONTENIDO PRINCIPAL */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .header-section { margin-bottom: 30px; }

        .selector-container {
            background: var(--bg-secundario);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--accent);
            display: inline-block;
            margin-bottom: 30px;
        }

        select {
            padding: 10px;
            background: var(--bg-principal);
            color: white;
            border: 1px solid var(--accent);
            border-radius: 5px;
            width: 300px;
            cursor: pointer;
        }

        #contenedor_tabla {
            margin-top: 20px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logout-btn {
            margin-top: auto;
            padding: 20px;
            text-align: center;
            color: #ffb3b3;
            text-decoration: none;
            font-weight: bold;
        }

        /* --- NUEVOS ESTILOS DEL MODAL DE CALIFICACIÓN --- */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: var(--bg-principal);
            border: 1px solid var(--accent);
            border-radius: 12px;
            width: 400px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        .modal-close {
            position: absolute;
            top: 15px; right: 20px;
            color: var(--subtexto);
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
        }
        .modal-close:hover { color: #ffb3b3; }

        .input-nota {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            margin-bottom: 15px;
            background: var(--bg-secundario);
            border: 1px solid var(--accent);
            color: white;
            border-radius: 5px;
            box-sizing: border-box;
            font-weight: bold;
        }

        .input-nota:focus { outline: none; border-color: var(--exito); }

        .btn-guardar-modal {
            width: 100%;
            padding: 12px;
            background: var(--exito);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-guardar-modal:hover { background: #27ae60; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 style="color: var(--exito); margin: 0;">ISIC</h2>
            <small>Panel de Control</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../docente.php">🏠 INICIO</a></li>
            <li><a href="../MateriasDocente/docenteM.php">📚 MIS MATERIAS</a></li>
            <li><a href="calificaciones.php" class="active">📝 CALIFICACIONES</a></li>
            <li><a href="../TareasDocente/docenteT.php">📂 TAREAS</a></li>
        </ul>
        <a href="../../auth/logout.php" class="logout-btn">CERRAR SESIÓN</a>
    </aside>

    <main class="main-content">
        <div class="header-section">
            <h1>Control de Calificaciones</h1>
            <p style="color: var(--subtexto);">Asigna las notas finales por cada unidad de tus grupos.</p>
        </div>

        <div class="selector-container">
            <label style="display: block; margin-bottom: 10px; font-weight: bold;">Selecciona un Grupo:</label>
            <select id="selector_grupo" onchange="cargarTablaCalificaciones(this.value)">
                <option value="">-- Seleccionar --</option>
                <?php while($g = mysqli_fetch_assoc($grupos)): ?>
                    <option value="<?php echo $g['id']; ?>"><?php echo $g['materia'] . " (" . $g['nombre_grupo'] . ")"; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="contenedor_tabla">
            <div style="text-align: center; padding: 50px; color: var(--subtexto); border: 2px dashed var(--accent); border-radius: 10px;">
                Selecciona un grupo del menú superior para cargar la sábana de notas.
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modal_calificar">
        <div class="modal-box">
            <span class="modal-close" onclick="cerrarModal()">×</span>
            <h2 style="margin-top:0; color:var(--exito);">Asignar Calificación</h2>
            <p id="modal_nombre_alumno" style="color:var(--subtexto); margin-bottom:20px; font-weight:bold;"></p>
            
            <form id="form_modal" onsubmit="return false;">
                <input type="hidden" id="modal_alumno_id">
                
                <div id="modal_inputs_contenedor"></div>
                
                <button type="button" class="btn-guardar-modal" id="btn_guardar_modal" onclick="guardarNotasModal()">💾 Guardar Calificaciones</button>
            </form>
        </div>
    </div>

    <script>
    function cargarTablaCalificaciones(grupoId) {
        if(!grupoId) {
            document.getElementById('contenedor_tabla').innerHTML = '<div style="text-align:center; padding:50px; color:var(--subtexto); border:2px dashed var(--accent); border-radius:10px;">Selecciona un grupo...</div>';
            return;
        }
        document.getElementById('contenedor_tabla').innerHTML = '<p style="text-align:center; color:var(--subtexto);">⏳ Cargando lista de alumnos...</p>';

        fetch('get_calificaciones_unidades.php?grupo_id=' + grupoId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenedor_tabla').innerHTML = html;
            });
    }

    // --- FUNCIONES DEL MODAL ---
    function abrirModalGraduar(alumnoId, nombre, notasJsonStr) {
        try {
            // ¡AQUÍ ESTÁ LA CORRECCIÓN! Usamos decodeURIComponent para desempaquetar la cadena
            const notas = JSON.parse(decodeURIComponent(notasJsonStr)); 
            
            document.getElementById('modal_alumno_id').value = alumnoId;
            document.getElementById('modal_nombre_alumno').innerText = 'Estudiante: ' + nombre.toUpperCase();
            
            let htmlInputs = '';
            notas.forEach(n => {
                let valorNota = (n.nota === null || n.nota === "") ? 0 : n.nota;
                
                htmlInputs += `<label style="display:block; margin-top:10px; color:var(--subtexto); font-size:12px;">UNIDAD: ${n.nombre.toUpperCase()}</label>`;
                htmlInputs += `<input type="number" step="0.1" min="0" max="100" class="input-nota" id="nota_uni_${n.unidad_id}" value="${valorNota}" 
                               oninput="if(this.value>100)this.value=100; if(this.value<0)this.value=0;"
                               onkeydown="return event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'">`;
            });
            
            document.getElementById('modal_inputs_contenedor').innerHTML = htmlInputs;
            document.getElementById('modal_calificar').style.display = 'flex';
        } catch (error) {
            console.error("Error al procesar JSON: ", error);
            alert("Hubo un error al procesar las calificaciones.");
        }
    }

    function cerrarModal() {
        document.getElementById('modal_calificar').style.display = 'none';
    }

    function guardarNotasModal() {
        const btn = document.getElementById('btn_guardar_modal');
        btn.innerText = '⏳ Guardando...';
        btn.disabled = true;
        
        const alumnoId = document.getElementById('modal_alumno_id').value;
        const inputs = document.querySelectorAll('#modal_inputs_contenedor input');
        
        let promesas = []; 
        
        inputs.forEach(input => {
            const unidadId = input.id.replace('nota_uni_', '');
            const nota = input.value;
            
            const data = new FormData();
            data.append('alumno_id', alumnoId);
            data.append('unidad_id', unidadId);
            data.append('nota', nota);
            
            // Usamos guardar_nota.php que es el archivo que acabamos de corregir
            promesas.push(fetch('guardar_nota.php', { method: 'POST', body: data }));
        });
        
        Promise.all(promesas).then(() => {
            cerrarModal();
            cargarTablaCalificaciones(document.getElementById('selector_grupo').value); 
            btn.innerText = '💾 Guardar Calificaciones';
            btn.disabled = false;
        });
    }
    </script>
</body>
</html>