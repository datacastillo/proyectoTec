<?php
session_start();
require_once '../config/db.php';

// Verificamos sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../auth/login.html");
    exit();
}

$nombre_docente = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente | ISIC</title>
    <link rel="stylesheet" href="docente.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../img/logoTec.png" class="logo-tec" alt="Logo">
                <div class="user-info">
                    <img src="../img/user.png" alt="User">
                    <span>DOC-2026-X</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="docente.php">INICIO ▼</a></li>
                    <li><a href="MateriasDocente/docenteM.php">MIS MATERIAS ◀</a></li>
                    <li><a href="MateriasDocente/docenteM.php">CALIFICACIONES ◀</a></li>
                    <li><a href="TareasDocente/docenteT.php">TAREAS ◀</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="menu-toggle">☰</div>
                <div class="isic-label">ISIC</div>
                <div class="logout-icon"><a href="../auth/logout.php" style="text-decoration:none;">🚪</a></div>
            </header>

            <div class="welcome-section">
                <h1>BIENVENIDO: <?php echo strtoupper($nombre_docente); ?></h1>
                <p>Panel de control del personal docente.</p>
                
                <div class="stats-container">
                    <div class="stat-card" onclick="location.href='MateriasDocente/docenteM.php'">
                        <h3>Grupos</h3>
                        <p class="active-text">Activos</p>
                    </div>
                    <div class="stat-card" onclick="location.href='TareasDocente/docenteT.php'">
                        <h3>Tareas</h3>
                        <p class="pending-text">Pendientes de Revisar</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>