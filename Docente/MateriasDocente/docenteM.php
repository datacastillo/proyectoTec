<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

$query_materias = "SELECT g.id as grupo_id, m.nombre as materia, g.nombre_grupo 
                   FROM grupos g 
                   JOIN materias m ON g.materia_id = m.id 
                   WHERE g.docente_id = '$id_docente'";

$resultado_materias = mysqli_query($conexion, $query_materias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Materias | ISIC</title>
    <link rel="stylesheet" href="../docente.css">
    <style>
        .grid-materias { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; padding: 20px; }
        .materia-card { 
            background: #142d3e; border-radius: 12px; padding: 25px; 
            border: 1px solid rgba(62, 146, 204, 0.2); position: relative; 
        }
        .materia-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #3e92cc; }
        .materia-card h3 { color: #fff; margin: 0 0 10px 0; text-transform: uppercase; font-size: 1.1rem; }
        .materia-card p { color: #adb5bd; margin: 5px 0; font-size: 14px; }
        .btn-ver { 
            display: inline-block; margin-top: 15px; padding: 10px 20px; 
            background: #3e92cc; color: white; text-decoration: none; 
            border-radius: 5px; font-size: 12px; font-weight: bold; transition: 0.3s;
        }
        .btn-ver:hover { background: #fff; color: #142d3e; }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../img/logoTec.png" class="logo-tec" alt="Logo">
            <div class="user-info">
                <span>DOCENTE:<br><b><?php echo strtoupper($nombre_docente); ?></b></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../docente.php">🏠 INICIO</a></li>
                <li class="active"><a href="docenteM.php">📘 MIS MATERIAS</a></li>
                <li><a href="../calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
                <li><a href="../TareasDocente/docenteT.php">📂 TAREAS</a></li>
                <li style="margin-top: 50px;"><a href="../../auth/logout.php" style="color: #ff4444;">🚪 SALIR</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">GESTIÓN DE ASIGNATURAS</div>
        </header>

        <section style="padding: 30px;">
            <h2 style="color: white; margin-bottom: 25px;">Mis Grupos Asignados</h2>
            
            <div class="grid-materias">
                <?php if($resultado_materias && mysqli_num_rows($resultado_materias) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($resultado_materias)): ?>
                        <div class="materia-card">
                            <h3><?php echo $row['materia']; ?></h3>
                            <p><b>Grupo:</b> <?php echo $row['nombre_grupo']; ?></p>
                            <a href="../calificaciones/calificaciones.php?grupo_id=<?php echo $row['grupo_id']; ?>" class="btn-ver">GESTIONAR CALIFICACIONES</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="background: rgba(255,255,255,0.05); color: #adb5bd; grid-column: 1/-1; text-align: center; padding: 50px; border-radius: 10px;">
                        <p>No se encontraron materias vinculadas a tu cuenta de docente.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>