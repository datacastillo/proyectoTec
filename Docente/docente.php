<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente | ISIC</title>
    <link rel="stylesheet" href="docente.css">
    <style>
        .user-info b { color: #3e92cc; text-transform: uppercase; }
        .welcome-box {
            background: #142d3e;
            padding: 40px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            margin-top: 20px;
        }
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card-link {
            background: #0d1b2a;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid rgba(62, 146, 204, 0.2);
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }
        .card-link:hover {
            transform: translateY(-5px);
            border-color: #3e92cc;
            background: #1a3a4a;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../img/logoTec.png" class="logo-tec" alt="Logo">
            <div class="user-info">
                <span>DOCENTE:<br><b><?php echo $nombre_docente; ?></b></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="active"><a href="docente.php">🏠 INICIO</a></li>
                <li><a href="MateriasDocente/docenteM.php">📘 MIS MATERIAS</a></li>
                <li><a href="MateriasDocente/docenteM.php">📝 CALIFICACIONES</a></li>
                <li><a href="TareasDocente/docenteT.php">📂 TAREAS</a></li>
                <li style="margin-top: 50px;"><a href="../auth/logout.php" style="color: #ff4444;">🚪 SALIR</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">SISTEMA INTEGRAL DE INFORMACIÓN CONTINUA</div>
            <div class="date-box" style="color: #3e92cc;"><?php echo date('d / m / Y'); ?></div>
        </header>

        <section style="padding: 30px;">
            <div class="welcome-box">
                <h1 style="font-size: 2.5rem; margin-bottom: 10px;">¡Bienvenido, <?php echo explode(' ', $nombre_docente)[0]; ?>!</h1>
                <p style="color: #adb5bd;">Panel de gestión académica para el Instituto de Sistemas Computacionales.</p>
                
                <div class="cards-container">
                    <a href="MateriasDocente/docenteM.php" class="card-link">
                        <h3>📘 Mis Materias</h3>
                        <p>Consulta tus grupos y listas de alumnos.</p>
                    </a>
                    <a href="MateriasDocente/docenteM.php" class="card-link">
                        <h3>📝 Calificaciones</h3>
                        <p>Sube y edita las notas de tus estudiantes.</p>
                    </a>
                    <a href="TareasDocente/docenteT.php" class="card-link">
                        <h3>📂 Tareas</h3>
                        <p>Administra las actividades y trabajos.</p>
                    </a>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>