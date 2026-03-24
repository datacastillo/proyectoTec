<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];

$query_tareas = "
    SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega_limite, m.nombre as materia
    FROM tareas t
    JOIN unidades u ON t.unidad_id = u.id
    JOIN grupos g ON u.grupo_id = g.id
    JOIN inscripciones i ON g.id = i.grupo_id
    JOIN alumnos a ON i.alumno_id = a.id
    JOIN materias m ON g.materia_id = m.id
    WHERE a.usuario_id = '$id_usuario'
    ORDER BY t.fecha_entrega_limite ASC";

$res_tareas = mysqli_query($conexion, $query_tareas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas | Tec San Pedro</title>
    <link rel="stylesheet" href="../Materias/styles.css">
    <link rel="stylesheet" href="tareas.css">
</head>
<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">
        <div class="top-actions">
            <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
            <div class="notificacion-container" onclick="toggleNotificaciones()">
                🔔 <span id="notif-dot" class="notif-dot"></span>
            </div>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>
            <ul class="menu">
                <li class="activo">TAREAS ▼</li>
                <li><a href="../Materias/index.php">MATERIAS ◀</a></li>
                <li><a href="../Calificaciones/calificaciones.php">CALIFICACIONES ◀</a></li>
                <li><a href="../Kardex/kardex.php">KARDEX ◀</a></li>
            </ul>
        </aside>

        <main class="contenido">
            <div class="cards-container">
                <?php 
                if(mysqli_num_rows($res_tareas) > 0) {
                    while($tarea = mysqli_fetch_assoc($res_tareas)) {
                        // Formateamos la fecha
                        $fecha = date("d/m", strtotime($tarea['fecha_entrega_limite']));
                ?>
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <h3><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                <p><?php echo htmlspecialchars($tarea['materia']); ?></p>
                            </div>
                            <div class="flip-card-back">
                                <p><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                <p><strong>Límite: <?php echo $fecha; ?></strong></p>
                                <button class="btn-subir" onclick="abrirModal(<?php echo $tarea['id']; ?>)">Subir PDF</button>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<p>No tienes tareas pendientes por ahora. </p>";
                }
                ?>
            </div>
        </main>
    </div>

    <div id="modalPDF" class="modal">
        <div class="modal-content">
            <h2>Subir Tarea</h2>
            <form action="subir_tarea.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tarea_id" id="modal_tarea_id">
                <input type="file" name="archivo_pdf" accept="application/pdf" required>
                <div class="modal-botones">
                    <button type="submit" class="btn-enviar">Subir</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal(id) {
            document.getElementById("modal_tarea_id").value = id;
            document.getElementById("modalPDF").style.display = "flex";
        }
        function cerrarModal() {
            document.getElementById("modalPDF").style.display = "none";
        }
        function toggleMenu() {
            document.querySelector(".sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>