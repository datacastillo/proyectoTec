<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'] ?? 'Alumno';

$query_alu = "SELECT matricula, carrera_id FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_alu = mysqli_query($conexion, $query_alu);
$reg_alu = mysqli_fetch_assoc($res_alu);

$matricula = $reg_alu['matricula'] ?? 'S/M';
$carrera_id = $reg_alu['carrera_id'] ?? 0;

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
    INNER JOIN inscripciones i ON g.id = i.grupo_id
    INNER JOIN alumnos a ON i.alumno_id = a.id
    LEFT JOIN entregas e ON t.id = e.tarea_id AND e.alumno_id = a.id
    WHERE a.usuario_id = '$id_usuario'
    GROUP BY t.id -- <--- ESTA LÍNEA ES LA CLAVE PARA ELIMINAR DUPLICADOS
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
    <title>Tareas Pendientes | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        .cards-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top: 25px; }
        .flip-card { background-color: transparent; width: 100%; height: 220px; perspective: 1000px; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; border-radius: 10px; }
        .flip-card:hover .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        
        .flip-card-front { background: #142d3e; border: 1px solid rgba(255,255,255,0.05); }
        .flip-card-back { background: #0d1b2a; border: 1px solid #3e92cc; transform: rotateY(180deg); align-items: center; }

        .materia-name { font-size: 0.85rem; color: #adb5bd; margin-bottom: 10px; font-weight: bold; letter-spacing: 1px; }
        .tarea-title { color: #fff; font-size: 1.2rem; font-weight: 900; }
        .status-check { color: #2ecc71; font-weight: bold; margin-top: 15px; font-size: 0.85rem; background: rgba(46, 204, 113, 0.1); padding: 5px 10px; border-radius: 5px; display: inline-block; }
        
        .tarea-desc { font-size: 0.9rem; color: #e0e1dd; margin-bottom: 15px; line-height: 1.4; }
        .fecha-limite { font-size: 0.85rem; color: #adb5bd; margin-bottom: 15px; }
        .fecha-vencida { color: #e74c3c; font-weight: bold; }

        /* Botones de acción */
        .btn-subir { background: #3e92cc; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; font-size: 13px; }
        .btn-subir:hover { background: #2c7bb6; }
        .entregada { background: rgba(46, 204, 113, 0.2) !important; color: #2ecc71; border: 1px solid #2ecc71; padding: 10px 15px; border-radius: 5px; width: 100%; font-weight: bold; cursor: default; font-size: 13px; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
        .modal-content { background: #142d3e; padding: 30px; border-radius: 10px; width: 350px; text-align: center; border: 1px solid #3e92cc; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .modal-content h2 { color: #fff; margin-bottom: 5px; font-size: 1.5rem; }
        .modal-content p { font-size: 13px; color: #adb5bd; margin-bottom: 20px; }
        .file-input { width: 100%; padding: 10px; background: #0d1b2a; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 5px; margin-bottom: 20px; }
        .btn-group { display: flex; gap: 10px; }
        .btn-enviar { background: #3e92cc; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.3s; }
        .btn-enviar:hover { background: #2c7bb6; }
        .btn-cerrar { background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.3s; }
        .btn-cerrar:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../img/logoTec.png" alt="Logo" style="max-width: 120px; margin-bottom: 10px;">
            <div class="user-info">
                <span style="color:#3e92cc; font-size: 12px; font-weight: bold;">ALUMNO:</span><br>
                <b style="color: white; font-size: 14px;"><?php echo strtoupper($nombreAlumno); ?></b><br>
                <span style="color: #adb5bd; font-size: 12px;">Matrícula: <?php echo $matricula; ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="../Materias/Index.php">📚 MIS MATERIAS</a></li>
                <li><a href="../Calificaciones/calificaciones.php">📊 CALIFICACIONES</a></li>
                <li class="active"><a href="tareas.php">📝 TAREAS PENDIENTES</a></li>
                <li><a href="../Kardex/kardex.php">📜 MI KARDEX</a></li>
                <li style="margin-top: 30px;"><a href="../../auth/logout.php" style="color: #e74c3c;">🚪 CERRAR SESIÓN</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
        </header>

        <section style="padding: 30px;">
            <h2 style="color: #fff; font-size: 2rem; margin-bottom: 5px;">Centro de Tareas</h2>
            <p style="color: #adb5bd; font-size: 1rem;">Revisa tus asignaciones, fechas de entrega y sube tus trabajos.</p>

            <div class="cards-container">
                <?php 
                if(mysqli_num_rows($res_tareas) > 0) {
                    while($tarea = mysqli_fetch_assoc($res_tareas)) {
                        $fecha_limite = strtotime($tarea['fecha_entrega_limite']);
                        $fecha_formato = ($tarea['fecha_entrega_limite']) ? date("d/m/Y H:i", $fecha_limite) : "Sin fecha límite";
                        $ya_entregado = !is_null($tarea['entrega_id']);
                ?>
                    <div class="flip-card">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <p class="materia-name"><?php echo strtoupper(htmlspecialchars($tarea['materia'])); ?></p>
                                <h3 class="tarea-title"><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                                <?php if($ya_entregado): ?>
                                    <div><span class="status-check">✔ ENTREGADA</span></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flip-card-back">
                                <p class="tarea-desc"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                
                                <p class="fecha-limite">Límite: <br>
                                    <span class="<?php echo ($fecha_limite < time() && $tarea['fecha_entrega_limite']) ? 'fecha-vencida' : 'text-white'; ?>">
                                        <?php echo $fecha_formato; ?>
                                    </span>
                                </p>
                                
                                <?php if($ya_entregado): ?>
                                    <button class="entregada" disabled>ARCHIVO ENVIADO</button>
                                <?php else: ?>
                                    <button class="btn-subir" onclick="abrirModal(<?php echo $tarea['id']; ?>)">↑ SUBIR ARCHIVO PDF</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<div style='grid-column: 1 / -1; text-align:center; padding: 50px; background: #142d3e; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);'>
                            <h3 style='color: #fff; margin-bottom: 10px;'>Sin tareas pendientes</h3>
                            <p style='color: #adb5bd;'>¡Excelente! No tienes actividades pendientes por el momento.</p>
                          </div>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

<div id="modalPDF" class="modal">
    <div class="modal-content">
        <h2>Subir Archivo</h2>
        <p>Asegúrate de que tu archivo esté en formato <strong>.PDF</strong></p>
        
        <form action="subir_tarea.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tarea_id" id="modal_tarea_id">
            <input type="file" name="archivo_pdf" accept="application/pdf" required class="file-input">
            
            <div class="btn-group">
                <button type="button" class="btn-cerrar" onclick="cerrarModal()">CANCELAR</button>
                <button type="submit" class="btn-enviar">ENVIAR TAREA</button>
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
</script>
</body>
</html>