<?php
session_start();
require_once '../../config/db.php';

// Verificación de seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html"); exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

// Obtener ID interno del docente
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// Consultar materias asignadas
$query_mats = "SELECT DISTINCT m.id, m.nombre FROM materias m 
               JOIN grupos g ON m.id = g.materia_id 
               WHERE g.docente_id = '$id_docente'";
$res_mats = mysqli_query($conexion, $query_mats);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificaciones | ISIC</title>
    <link rel="stylesheet" href="docenteM.css">
    <style>
        /* Estilos del Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
        .modal-content { background: #1a1a1a; padding: 25px; border-radius: 10px; border: 1px solid #444; width: 300px; color: white; text-align: center; }
        .modal-content input { width: 90%; padding: 10px; margin: 15px 0; border-radius: 5px; border: none; font-size: 1.2rem; text-align: center; background: #000; color: #fff; border: 1px solid #333; }
        .btn-save { background: #00ff00; color: black; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; }
        .btn-cancel { background: #ff4444; color: white; border: none; padding: 8px; width: 100%; margin-top: 10px; cursor: pointer; border-radius: 5px; }
        .sub-active { background: #333 !important; border-left: 4px solid #00ff00; }
        .grupo-btn { background: #222; color: #fff; border: 1px solid #444; padding: 8px 15px; cursor: pointer; border-radius: 5px; transition: 0.3s; }
        .grupo-btn:hover { background: #00ff00; color: #000; }
        .grupo-btn.active { background: #00ff00; color: #000; font-weight: bold; }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../img/logoTec.png" class="logo-tec" alt="Logo">
            <div class="user-info">
                <span style="color: #00ff00; font-weight: bold;"><?php echo htmlspecialchars($nombre_docente); ?></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="menu-header">MIS MATERIAS ▼</div>
            <ul class="submenu show">
                <?php while($m = mysqli_fetch_assoc($res_mats)): ?>
                    <li onclick="cargarGrupos(<?php echo $m['id']; ?>, this)"><?php echo strtoupper($m['nombre']); ?></li>
                <?php endwhile; ?>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button onclick="location.href='../docente.php'" style="background:none; border:none; color:white; font-size:20px; cursor:pointer;">🏠 Inicio</button>
            <div class="isic-label">ISIC</div>
        </header>

        <div class="content-body">
            <div id="contenedorGrupos" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;"></div>
            <div class="table-container">
                <h2 id="nombreGrupoActual">Seleccione Materia</h2>
                <table class="calificaciones-table" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ALUMNO</th><th>U1</th><th>U2</th><th>U3</th><th>U4</th><th>PROM</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAlumnos">
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Haga clic en una materia para comenzar</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modalCal" class="modal">
    <div class="modal-content">
        <h3 style="color: #00ff00;">Calificar Unidad</h3>
        <p id="nombreAlumnoModal" style="font-size: 0.8rem; color: #aaa;"></p>
        <form id="formCalificar">
            <input type="hidden" id="m_alumno_id">
            <input type="hidden" id="m_unidad_num">
            <input type="hidden" id="m_grupo_id">
            <label>Calificación (0-100):</label>
            <input type="number" id="notaInput" min="0" max="100" step="0.1" required>
            <button type="submit" id="btnGuardar" class="btn-save">GUARDAR</button>
            <button type="button" class="btn-cancel" onclick="cerrarModal()">CANCELAR</button>
        </form>
    </div>
</div>

<script>
    let grupoActivoId = 0;
    let nombreGrupoActual = "";

    function cargarGrupos(m_id, el) {
        document.querySelectorAll('.submenu li').forEach(li => li.classList.remove('sub-active'));
        el.classList.add('sub-active');
        
        fetch('get_grupos.php?materia_id=' + m_id)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(g => {
                    html += `<button class="grupo-btn" onclick="cargarAlumnos(${g.id}, '${g.nombre_grupo}', this)">${g.nombre_grupo}</button>`;
                });
                document.getElementById('contenedorGrupos').innerHTML = html;
                document.getElementById('tablaAlumnos').innerHTML = '<tr><td colspan="6" style="text-align:center;">Seleccione un grupo</td></tr>';
                document.getElementById('nombreGrupoActual').innerText = "Seleccione un Grupo";
            });
    }

    function cargarAlumnos(g_id, nombre, btn) {
        grupoActivoId = g_id;
        nombreGrupoActual = nombre;
        document.getElementById('nombreGrupoActual').innerText = "Grupo: " + nombre;
        
        document.querySelectorAll('.grupo-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');

        fetch('get_alumnos.php?grupo_id=' + g_id)
            .then(res => res.text())
            .then(html => {
                document.getElementById('tablaAlumnos').innerHTML = html;
            });
    }

    function abrirModal(al_id, uni, nota, gr_id, nombreAl) {
        document.getElementById('m_alumno_id').value = al_id;
        document.getElementById('m_unidad_num').value = uni;
        document.getElementById('m_grupo_id').value = gr_id;
        document.getElementById('nombreAlumnoModal').innerText = "Alumno: " + nombreAl + " - Unidad " + uni;
        document.getElementById('notaInput').value = (nota === '-' ? '' : nota);
        document.getElementById('modalCal').style.display = 'flex';
        setTimeout(() => document.getElementById('notaInput').focus(), 100);
    }

    function cerrarModal() { document.getElementById('modalCal').style.display = 'none'; }

    document.getElementById('formCalificar').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardar');
        const nota = document.getElementById('notaInput').value;
        
        btn.innerText = "Cargando...";
        btn.disabled = true;

        const datos = new FormData();
        datos.append('alumno_id', document.getElementById('m_alumno_id').value);
        datos.append('unidad', document.getElementById('m_unidad_num').value);
        datos.append('nota', nota);
        datos.append('grupo_id', document.getElementById('m_grupo_id').value);

        fetch('guardar_calificacion.php', {
            method: 'POST',
            body: datos
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (err) {
                console.error("Respuesta no JSON:", text);
                throw new Error("El servidor no respondió correctamente. Revisa la consola F12.");
            }
        })
        .then(data => {
            if(data.success) {
                cerrarModal();
                cargarAlumnos(grupoActivoId, nombreGrupoActual);
            } else {
                alert("Error al guardar: " + data.error);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message);
        })
        .finally(() => {
            btn.innerText = "GUARDAR";
            btn.disabled = false;
        });
    });

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == document.getElementById('modalCal')) cerrarModal();
    }
</script>
</body>
</html>