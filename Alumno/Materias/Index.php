<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'];
$matricula = $_SESSION['matricula'];

// --- NUEVA CONSULTA: Traer materias de la carrera del alumno ---
// Buscamos la carrera_id del alumno y luego sus materias
$query_materias = "SELECT m.nombre, m.clave 
                   FROM materias m
                   JOIN alumnos a ON m.carrera_id = a.carrera_id
                   WHERE a.usuario_id = '$id_usuario'";

$res_materias = mysqli_query($conexion, $query_materias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno | Tec San Pedro</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">
        <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img" alt="Usuario">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>

            <ul class="menu">
                <li onclick="toggleMaterias()">MIS MATERIAS ▼</li>

                <ul id="materiasLista" class="submenu">
                    <?php 
                    // Generamos la lista automáticamente desde la BD
                    if(mysqli_num_rows($res_materias) > 0) {
                        while($materia = mysqli_fetch_assoc($res_materias)) {
                            $nom = $materia['nombre'];
                            $clave = $materia['clave'];
                            echo "<li onclick=\"mostrarMateria('$clave')\">" . strtoupper($nom) . "</li>";
                        }
                    } else {
                        echo "<li>Sin materias asignadas</li>";
                    }
                    ?>
                </ul>

                <li><a href="../Calificaciones/calificaciones.php" class="menu-link">CALIFICACIONES ▼</a></li>
                <li><a href="../Tareas/tareas.php" class="menu-link">TAREAS ▼</a></li>
                <li><a href="../Kardex/kardex.php" class="menu-link">KARDEX ▼</a></li>
            </ul>
        </aside>

        <main class="contenido" id="contenido">
            <h1>BIENVENIDO, <br><span><?php echo strtoupper($nombreAlumno); ?></span></h1>
            <p style="margin-top: 15px; color: #666;">Selecciona una materia del menú para ver tu avance académico.</p>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>