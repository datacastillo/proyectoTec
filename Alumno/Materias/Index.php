<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

// Estas variables ahora vienen cargadas correctamente desde tu login_proceso.php
$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'];
$matricula = $_SESSION['matricula'];

/**
 * CONSULTA OPTIMIZADA:
 * Buscamos las materias que pertenecen a la carrera del alumno.
 * No usamos 'inscripciones' ni 'grupos' porque aún están vacías en tu BD.
 */
$query_materias = "SELECT m.nombre, m.clave 
                   FROM materias m
                   INNER JOIN alumnos a ON m.carrera_id = a.carrera_id
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
        <div class="menu-btn">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">
        <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img" alt="Usuario" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($nombreAlumno); ?>'">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>

            <ul class="menu">
                <li onclick="toggleMaterias()" style="cursor:pointer; font-weight:bold; color: white; list-style: none; padding: 10px;">
                    MIS MATERIAS ▼
                </li>

                <ul id="materiasLista" class="submenu" style="display: none; padding-left: 20px;">
                    <?php 
                    if($res_materias && mysqli_num_rows($res_materias) > 0) {
                        while($materia = mysqli_fetch_assoc($res_materias)) {
                            $nom = $materia['nombre'];
                            $clave = $materia['clave'];
                            echo "<li onclick=\"mostrarMateria('$clave')\" style='cursor:pointer; color: #ccc; padding: 5px 0; list-style: none;'>" . strtoupper($nom) . "</li>";
                        }
                    } else {
                        echo "<li style='color: #888; list-style: none; font-size: 0.8em;'>Sin materias asignadas</li>";
                    }
                    ?>
                </ul>

                <li style="list-style: none;"><a href="../Calificaciones/calificaciones.php" class="menu-link" style="text-decoration: none; color: white; display: block; padding: 10px;">CALIFICACIONES</a></li>
                <li style="list-style: none;"><a href="../Tareas/tareas.php" class="menu-link" style="text-decoration: none; color: white; display: block; padding: 10px;">TAREAS</a></li>
                <li style="list-style: none;"><a href="../Kardex/kardex.php" class="menu-link" style="text-decoration: none; color: white; display: block; padding: 10px;">KARDEX</a></li>
            </ul>
        </aside>

        <main class="contenido" id="contenido">
            <h1>BIENVENIDO, <br><span style="color: #0044cc;"><?php echo strtoupper($nombreAlumno); ?></span></h1>
            <p style="margin-top: 15px; color: #666;">Selecciona una materia del menú lateral para ver tu avance académico.</p>
        </main>
    </div>

    <script>
        function toggleMaterias() {
            var lista = document.getElementById("materiasLista");
            lista.style.display = (lista.style.display === "none" || lista.style.display === "") ? "block" : "none";
        }

        function mostrarMateria(clave) {
            document.getElementById('contenido').innerHTML = "<h1>Materia: " + clave + "</h1><p>Contenido de la materia cargado correctamente.</p>";
        }
    </script>
</body>
</html>