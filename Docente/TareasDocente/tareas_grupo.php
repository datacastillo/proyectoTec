<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit;
}

$grupo_id = $_GET['grupo_id'] ?? 0;

// Obtener info del grupo y materia
$info = mysqli_query($conexion, "SELECT g.nombre_grupo, m.nombre FROM grupos g JOIN materias m ON g.materia_id = m.id WHERE g.id = '$grupo_id'");
$datos = mysqli_fetch_assoc($info);

// Buscamos si el grupo ya tiene una unidad (para asignar la tarea ahí)
$res_u = mysqli_query($conexion, "SELECT id FROM unidades WHERE grupo_id = '$grupo_id' LIMIT 1");
$unidad = mysqli_fetch_assoc($res_u);
$unidad_id = $unidad['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Grupo <?php echo $datos['nombre_grupo']; ?> | ISIC</title>
    <style>
        body { margin: 0; font-family: Arial; background: #0f3145; color: white; }
        .container-tareas { padding: 40px; max-width: 1100px; margin: 0 auto; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2b6671; padding-bottom: 15px; margin-bottom: 30px; }
        .btn-crear { background: #2ecc71; color: #0f3145; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-crear:hover { background: #27ae60; transform: scale(1.05); }

        .seccion-blanca { background: #255b68; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        select { padding: 12px; border-radius: 5px; background: #0f3145; color: white; border: 1px solid #2b6671; width: 100%; max-width: 500px; font-size: 16px; }

        /* Estilos del Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: #1e4d58; padding: 30px; border-radius: 10px; width: 450px; border: 2px solid #2b6671; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; background: #0f3145; color: white; border: 1px solid #2b6671; border-radius: 5px; box-sizing: border-box; }
        .btn-publicar { background: #2ecc71; color: #0f3145; width: 100%; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="container-tareas">
        <a href="docenteT.php" style="color: #88d4e8; text-decoration: none; font-weight: bold;">⬅ Volver a mis grupos</a>
        
        <div class="header-flex">
            <div>
                <h1 style="margin: 0;"><?php echo $datos['nombre']; ?></h1>
                <p style="margin: 5px 0 0 0; color: #adb5bd;">Grupo: <?php echo $datos['nombre_grupo']; ?></p>
            </div>
            <button class="btn-crear" onclick="abrirModal()">➕ Nueva Tarea</button>
        </div>

        <div class="seccion-blanca">
            <label style="display: block; margin-bottom: 10px; font-weight: bold;">Selecciona una tarea para revisar entregas:</label>
            <select id="selector_tareas" onchange="cargarEntregas(this.value)">
                <option value="">-- Elige una tarea de la lista --</option>
                <?php
                $tareas = mysqli_query($conexion, "SELECT id, titulo FROM tareas WHERE unidad_id IN (SELECT id FROM unidades WHERE grupo_id = '$grupo_id') ORDER BY id DESC");
                while($t = mysqli_fetch_assoc($tareas)){
                    echo "<option value='{$t['id']}'>{$t['titulo']}</option>";
                }
                ?>
            </select>
        </div>

        <div id="contenedor_tabla_entregas">
            <p style="text-align:center; color: #adb5bd; padding: 50px;">Selecciona una tarea arriba para ver el listado de alumnos.</p>
        </div>
    </div>

    <div id="modalTarea" class="modal">
        <div class="modal-content">
            <h2 style="margin-top: 0; color: #2ecc71;">Crear Nueva Tarea</h2>
            <form id="formNuevaTarea">
                <input type="hidden" name="unidad_id" value="<?php echo $unidad_id; ?>">
                
                <label>Título:</label>
                <input type="text" name="titulo" placeholder="Ej: Práctica 1 - HTML" required>

                <label>Descripción:</label>
                <textarea name="descripcion" rows="4" placeholder="Instrucciones para los alumnos..."></textarea>

                <label>Fecha Límite:</label>
                <input type="datetime-local" name="fecha" required>

                <button type="submit" class="btn-publicar">🚀 Publicar Tarea</button>
                <button type="button" onclick="cerrarModal()" style="background:none; color:#ffb3b3; border:none; width:100%; margin-top:10px; cursor:pointer;">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
    function abrirModal() { document.getElementById('modalTarea').style.display = 'flex'; }
    function cerrarModal() { document.getElementById('modalTarea').style.display = 'none'; }

    function cargarEntregas(tareaId) {
        if(!tareaId) return;
        fetch('get_entregas.php?tarea_id=' + tareaId)
            .then(res => res.text())
            .then(html => { document.getElementById('contenedor_tabla_entregas').innerHTML = html; });
    }

    document.getElementById('formNuevaTarea').onsubmit = function(e) {
        e.preventDefault();
        fetch('guardar_tarea.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("✅ Tarea publicada");
                location.reload();
            } else {
                alert("❌ Error: " + data.error);
            }
        });
    };

    function guardarPuntosTarea(idEntrega) {
        const puntos = document.getElementById('puntos_' + idEntrega).value;
        const datos = new FormData();
        datos.append('id', idEntrega);
        datos.append('puntos', puntos);

        fetch('actualizar_nota.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(data => {
            if(data.success) alert("✅ Calificación guardada");
            else alert("❌ Error: " + data.error);
        });
    }
    </script>
</body>
</html>