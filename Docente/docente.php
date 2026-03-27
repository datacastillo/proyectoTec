<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

// 1. Obtener ID del docente
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// 2. Consultas para los textos descriptivos (Opcional pero ayuda a la interfaz)
$res_count_grupos = mysqli_query($conexion, "SELECT COUNT(*) as total FROM grupos WHERE docente_id = '$id_docente'");
$total_grupos = mysqli_fetch_assoc($res_count_grupos)['total'] ?? 0;

$res_count_tareas = mysqli_query($conexion, "SELECT COUNT(e.id) as total FROM entregas e 
                    INNER JOIN tareas t ON e.tarea_id = t.id 
                    INNER JOIN unidades u ON t.unidad_id = u.id 
                    INNER JOIN grupos g ON u.grupo_id = g.id 
                    WHERE g.docente_id = '$id_docente' AND e.puntos_obtenidos = 0");
$tareas_pendientes = mysqli_fetch_assoc($res_count_tareas)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente | ISIC</title>
    <style>
        :root {
            --bg-principal: #0f3145;
            --bg-secundario: #255b68;
            --accent: #2b6671;
            --texto: #ffffff;
            --subtexto: #adb5bd;
            --exito: #2ecc71;
        }

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-principal);
            color: var(--texto);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background-color: var(--bg-secundario);
            border-right: 1px solid var(--accent);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu { list-style: none; padding: 0; margin: 0; }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: var(--subtexto);
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: var(--bg-principal);
            color: white;
            border-left: 4px solid var(--exito);
        }

        /* CONTENIDO */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .welcome-header {
            margin-bottom: 40px;
            text-align: left;
        }

        .welcome-header h1 {
            font-size: 2.5rem;
            margin: 0;
            color: var(--texto);
        }

        /* GRID DE TARJETAS */
        .cards-grid {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .card {
            background: var(--bg-secundario);
            border: 1px solid var(--accent);
            border-radius: 12px;
            padding: 30px;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            width: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: var(--exito);
            background: var(--accent);
        }

        .card-icon { font-size: 3.5rem; margin-bottom: 15px; }

        .card h3 { margin: 10px 0; font-size: 1.4rem; }

        .card p {
            font-size: 0.9rem;
            color: var(--subtexto);
            margin: 0;
            line-height: 1.4;
        }

        .logout-btn {
            margin-top: auto;
            padding: 20px;
            text-align: center;
            color: #ffb3b3;
            text-decoration: none;
            font-weight: bold;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 style="color: var(--exito); margin:0;">ISIC</h2>
            <small>Panel Docente</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="docente.php" class="active">🏠 INICIO</a></li>
            <li><a href="MateriasDocente/docenteM.php">📚 MIS MATERIAS</a></li>
            <li><a href="Calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
            <li><a href="TareasDocente/docenteT.php">📂 TAREAS</a></li>
        </ul>
        <a href="../auth/logout.php" class="logout-btn">CERRAR SESIÓN</a>
    </aside>

    <main class="main-content">
        <div class="welcome-header">
            <h1>¡Bienvenido, <?php echo explode(' ', $nombre_docente)[0]; ?>!</h1>
            <p style="color: var(--subtexto); font-size: 1.1rem;">Gestión administrativa del ciclo escolar.</p>
        </div>

        <div class="cards-grid">
            <a href="MateriasDocente/docenteM.php" class="card">
                <div class="card-icon">📖</div>
                <h3>Mis Materias</h3>
                <p>Tienes <b><?php echo $total_grupos; ?></b> grupos asignados. Revisa tus listas de alumnos.</p>
            </a>

            <a href="Calificaciones/calificaciones.php" class="card">
                <div class="card-icon">📊</div>
                <h3>Calificaciones</h3>
                <p>Captura de notas finales por unidad y seguimiento académico.</p>
            </a>

            <a href="TareasDocente/docenteT.php" class="card">
                <div class="card-icon">📁</div>
                <h3>Tareas</h3>
                <p>Hay <b><?php echo $tareas_pendientes; ?></b> entregas nuevas esperando calificación.</p>
            </a>
        </div>
    </main>

</body>
</html>