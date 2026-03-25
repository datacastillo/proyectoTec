<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$res_doc = mysqli_query($conexion, "SELECT id, numero_empleado FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'];

$query_unidades = "SELECT u.id, m.nombre as materia, g.nombre_grupo, u.numero_unit 
                   FROM unidades u 
                   JOIN grupos g ON u.grupo_id = g.id 
                   JOIN materias m ON g.materia_id = m.id 
                   WHERE g.docente_id = '$id_docente'";
$res_unidades = mysqli_query($conexion, $query_unidades);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas Docente</title>
    <link rel="stylesheet" href="docenteT.css">
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="logo-box">
            <img src="../../Alumno/img/logoTec.png" class="logo-tec">
        </div>

        <div class="user-box">
            <img src="../img/user-icon.png">
            <span><?php echo $doc['numero_empleado']; ?></span>
        </div>

        <div class="menu">
            <li><a href="../MateriasDocente/docenteM.php">MATERIAS <span>◀</span></a></li>
            <li><a href="docenteT.php">TAREAS <span>▼</span></a></li>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="top-actions">
                <button class="add-btn" onclick="abrirModal()">+</button>
                <a href="../docente.php" class="home-btn">🏠</a>
                <div class="menu-toggle" onclick="toggleMenu()">☰</div>
            </div>
        </div>

        <div class="cards" id="cardsContainer">
            <?php
            $query_tareas = "SELECT t.id, t.titulo, m.nombre as materia, g.nombre_grupo 
                             FROM tareas t 
                             JOIN unidades u ON t.unidad_id = u.id 
                             JOIN grupos g ON u.grupo_id = g.id 
                             JOIN materias m ON g.materia_id = m.id 
                             WHERE g.docente_id = '$id_docente'";
            $res_tareas = mysqli_query($conexion, $query_tareas);
            
            while($t = mysqli_fetch_assoc($res_tareas)): ?>
                <div class="card" onclick="verEntregas(<?php echo $t['id']; ?>)">
                    <h3><?php echo $t['titulo']; ?></h3>
                    <p><?php echo $t['materia'] . " - " . $t['nombre_grupo']; ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <div id="vistaTarea" class="hidden">
            <button onclick="volver()" style="margin-bottom:20px; cursor:pointer;">⬅ Volver</button>
            <table class="tabla-tarea">
                <thead>
                    <tr>
                        <th>ALUMNO</th>
                        <th>TAREA</th>
                        <th>LINK</th>
                        <th>ESTATUS</th>
                    </tr>
                </thead>
                <tbody id="tablaTareaBody"></tbody>
            </table>
        </div>
    </main>
</div>

<div class="modal" id="modalTarea">
    <div class="modal-box">
        <form id="formNuevaTarea">
            <h3>Título tarea</h3>
            <input type="text" name="titulo" required>

            <h3>Descripción</h3>
            <textarea name="descripcion" style="width:100%; border-radius:10px; padding:10px;" required></textarea>

            <h3>Fecha Límite</h3>
            <input type="date" name="fecha" required>

            <h3>Seleccionar Unidad (Grupo - Materia)</h3>
            <select name="unidad_id" required>
                <option value="">Seleccionar...</option>
                <?php mysqli_data_seek($res_unidades, 0); // Reiniciar puntero ?>
                <?php while($u = mysqli_fetch_assoc($res_unidades)): ?>
                    <option value="<?php echo $u['id']; ?>">
                        <?php echo "{$u['materia']} - {$u['nombre_grupo']} (U{$u['numero_unit']})"; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" style="margin-top:20px; cursor:pointer;">GUARDAR</button>
            <button type="button" onclick="cerrarModal()" style="background:gray; cursor:pointer;">CANCELAR</button>
        </form>
    </div>
</div>

<script>
    function abrirModal() { document.getElementById('modalTarea').style.display = 'flex'; }
    function cerrarModal() { document.getElementById('modalTarea').style.display = 'none'; }

    // Guardar Tarea vía AJAX
    document.getElementById('formNuevaTarea').onsubmit = function(e) {
        e.preventDefault();
        fetch('guardar_tarea.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    };

    function verEntregas(tareaId) {
        document.getElementById('cardsContainer').classList.add('hidden');
        document.getElementById('vistaTarea').classList.remove('hidden');
        
        fetch(`get_entregas.php?tarea_id=${tareaId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('tablaTareaBody').innerHTML = html;
            });
    }

    function volver() {
        document.getElementById('cardsContainer').classList.remove('hidden');
        document.getElementById('vistaTarea').classList.add('hidden');
    }

    function toggleMenu() { document.querySelector(".sidebar").classList.toggle("active"); }
</script>

</body>
</html>