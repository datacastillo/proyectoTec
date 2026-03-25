<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];

// CONSULTA DE TAREAS CORREGIDA: u.grupo_id en lugar de u.group_id
$query_tareas = "
    SELECT 
        t.id, 
        t.titulo, 
        t.descripcion, 
        t.fecha_entrega_limite, 
        m.nombre as materia,
        e.id as entrega_id,
        e.estatus as entrega_estatus
    FROM tareas t
    INNER JOIN unidades u ON t.unidad_id = u.id
    INNER JOIN grupos g ON u.grupo_id = g.id
    INNER JOIN materias m ON g.materia_id = m.id
    INNER JOIN alumnos a ON m.carrera_id = a.carrera_id
    LEFT JOIN entregas e ON t.id = e.tarea_id AND e.alumno_id = a.id
    WHERE a.usuario_id = '$id_usuario'
    ORDER BY t.fecha_entrega_limite ASC";

$res_tareas = mysqli_query($conexion, $query_tareas);

if (!$res_tareas) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas | Tec San Pedro</title>
    <link rel="stylesheet" href="../Materias/styles.css">
    <link rel="stylesheet" href="tareas.css">
    <style>
        .entregada { background-color: #28a745 !important; cursor: default; color: white; border: none; padding: 10px; border-radius: 5px; }
        .fecha-vencida { color: #dc3545; font-weight: bold; }
        .status-check { color: #28a745; font-weight: bold; display: block; margin-top: 5px; }
    </style>
</head>
<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">
        <div class="top-actions">
            <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
            <div class="notificacion-container">🔔</div>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img" onerror="this.src='https://ui-avatars.com/api/?name=User'">
                <span class="user-id"><?php echo htmlspecialchars($matricula); ?></span>
            </div>
            <ul class="menu">
                <li class="activo">TAREAS ▼</li>
                <li><a href="../Materias/index.php" style="color:white; text-decoration:none; display:block; padding:10px;">MATERIAS ◀</a></li>
                <li><a href="../Calificaciones/calificaciones.php" style="color:white; text-decoration:none; display:block; padding:10px;">CALIFICACIONES ◀</a></li>
                <li><a href="../Kardex/kardex.php" style="color:white; text-decoration:none; display:block; padding:10px;">KARDEX ◀</a></li>
            </ul>
        </aside>

        <main class="contenido">
            <div class="cards-container">
                <?php 
                if(mysqli_num_rows($res_tareas) > 0) {
                    while($tarea = mysqli_fetch_assoc($res_tareas)) {
                        $fecha_limite = strtotime($tarea['fecha_entrega_limite']);
                        $fecha_formato = ($tarea['fecha_entrega_limite']) ? date("d/m H:i", $fecha_limite) : "Sin fecha";
                        $ya_entregado = !is_null($tarea['entrega_id']);
                ?>
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <h3><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                <p><strong><?php echo htmlspecialchars($tarea['materia']); ?></strong></p>
                                <?php if($ya_entregado): ?>
                                    <span class="status-check">✅ Tarea Entregada</span>
                                <?php endif; ?>
                            </div>
                            <div class="flip-card-back">
                                <p><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                <p>Límite: <span class="<?php echo ($fecha_limite < time() && $tarea['fecha_entrega_limite']) ? 'fecha-vencida' : ''; ?>">
                                    <?php echo $fecha_formato; ?>
                                </span></p>
                                
                                <?php if($ya_entregado): ?>
                                    <button class="entregada" disabled>Enviado</button>
                                <?php else: ?>
                                    <button class="btn-subir" onclick="abrirModal(<?php echo $tarea['id']; ?>)" style="background: #0044cc; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">Subir PDF</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<div style='text-align:center; width:100%; color:white; margin-top:50px;'>
                            <h2>Sin tareas pendientes</h2>
                            <p>No hay actividades registradas para tu carrera actualmente.</p>
                          </div>";
                }
                ?>
            </div>
        </main>
    </div>

    <div id="modalPDF" class="modal" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div class="modal-content" style="background: white; padding: 25px; border-radius: 10px; width: 320px; text-align: center;">
            <h2 style="color: #333;">Subir Archivo</h2>
            <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Solo se aceptan formatos PDF</p>
            <form action="subir_tarea.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tarea_id" id="modal_tarea_id">
                <input type="file" name="archivo_pdf" accept="application/pdf" required>
                <br><br>
                <div style="display: flex; justify-content: space-between;">
                    <button type="submit" style="background: #0044cc; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Enviar</button>
                    <button type="button" onclick="cerrarModal()" style="background: #cc0000; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Cancelar</button>
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