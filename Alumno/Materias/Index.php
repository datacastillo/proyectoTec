<?php
session_start();
require_once '../../config/db.php'; 

// 1. Validar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'] ?? 'Alumno';

// Consulta de información del alumno
$query_info = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_info = mysqli_query($conexion, $query_info);
$info_alumno = mysqli_fetch_assoc($res_info);

$alumno_id = $info_alumno['id'] ?? 0;
$matricula = $info_alumno['matricula'] ?? 'S/N';

// Consulta de materias dinámicas
$query_materias = "SELECT DISTINCT m.nombre, m.clave 
                   FROM materias m
                   INNER JOIN grupos g ON m.id = g.materia_id
                   INNER JOIN inscripciones i ON g.id = i.grupo_id
                   WHERE i.alumno_id = '$alumno_id'";
$res_materias = mysqli_query($conexion, $query_materias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* Paleta de colores Azul (Estilo Docente) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* Barra Lateral */
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        /* Contenido Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        /* Tarjetas de Materias */
        .grid-materias { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 25px; }
        .card-materia { background: #142d3e; border: 1px solid rgba(255,255,255,0.05); border-radius: 10px; padding: 25px; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; }
        .card-materia:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); border-color: #3e92cc; }
        .card-title { font-size: 1.2rem; font-weight: 900; color: #fff; margin-bottom: 10px; }
        .card-clave { font-size: 0.85rem; color: #3e92cc; font-weight: bold; margin-bottom: 20px; }
        .btn-entrar { background: rgba(62, 146, 204, 0.1); color: #3e92cc; border: 1px solid #3e92cc; padding: 8px 15px; border-radius: 5px; font-weight: bold; width: 100%; transition: 0.3s; cursor: pointer; }
        .card-materia:hover .btn-entrar { background: #3e92cc; color: #fff; }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../img/logoTec.png" alt="Logo" style="max-width: 120px; margin-bottom: 10px;">
            <div class="user-info">
                <span style="color:#3e92cc; font-size: 12px; font-weight: bold;">ALUMNO:</span><br>
                <b style="color: white; font-size: 14px;"><?php echo strtoupper($nombreAlumno); ?></b><br>
                <span style="color: #adb5bd; font-size: 12px;">Matrícula: <?php echo $matricula; ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="active"><a href="Index.php">📚 MIS MATERIAS</a></li>
                <li><a href="../Calificaciones/calificaciones.php">📊 CALIFICACIONES</a></li>
                <li><a href="../Tareas/tareas.php">📝 TAREAS PENDIENTES</a></li>
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
            <h2 style="color: #fff; font-size: 2.2rem; margin-bottom: 5px;">Bienvenido, <?php echo strtoupper($nombreAlumno); ?></h2>
            <p style="color: #adb5bd; font-size: 1.1rem; margin-bottom: 25px;">Selecciona una materia para ver sus detalles y recursos.</p>

            <div class="grid-materias">
                <?php 
                if($res_materias && mysqli_num_rows($res_materias) > 0) {
                    while($materia = mysqli_fetch_assoc($res_materias)) {
                        echo "
                        <div class='card-materia' onclick=\"location.href='../Tareas/tareas.php?materia_clave=".$materia['clave']."'\">
                            <div class='card-title'>".strtoupper($materia['nombre'])."</div>
                            <div class='card-clave'>CLAVE: ".$materia['clave']."</div>
                            <button class='btn-entrar'>IR A LA MATERIA ➔</button>
                        </div>";
                    }
                } else {
                    echo "<p style='color: #adb5bd; font-size: 1rem;'>No tienes materias inscritas en este semestre.</p>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>