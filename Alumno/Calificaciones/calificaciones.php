<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];


$query_materias = "SELECT m.nombre 
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
    <title>Calificaciones</title>
    <link rel="stylesheet" href="calificaciones.css">
</head>

<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">

        <div class="top-actions">
            <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
            
            <div class="notificacion-container" onclick="toggleNotificaciones()">
                🔔
                <span id="notif-dot" class="notif-dot"></span>
            </div>
        </div>
    </header>

    <div class="container">

        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img" alt="Usuario">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>

            <ul class="menu">
                <li class="activo">CALIFICACIONES ▼</li>
                <li>
                    <a href="../Materias/index.php">MATERIAS ◀</a>
                </li>
                <li>
                    <a href="../Tareas/tareas.php">TAREAS ◀</a>
                </li>
                <li>
                    <a href="../Kardex/kardex.php">KARDEX ◀</a>
                </li>
            </ul>
        </aside>

        <main class="contenido">
            <div class="contenedor-tabla">
                <table class="tabla-calificaciones">
                    <thead>
                        <tr>
                            <th>MATERIA</th>
                            <th>U1</th>
                            <th>U2</th>
                            <th>U3</th>
                            <th>U4</th>
                            <th>UF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($res_materias) > 0) {
                            while($fila = mysqli_fetch_assoc($res_materias)) {
                                echo "<tr>";
                                echo "<td class='materia'>" . strtoupper($fila['nombre']) . "</td>";
                                echo "<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No hay materias registradas</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function toggleMenu() {
            document.querySelector(".sidebar").classList.toggle("active");
        }

        function activarNotificacion() {
            document.getElementById("notif-dot").style.display = "block";
        }

        function limpiarNotificacion() {
            document.getElementById("notif-dot").style.display = "none";
        }

        function toggleNotificaciones() {
            alert("Aquí van las notificaciones 🔔");
            limpiarNotificacion();
        }

        activarNotificacion();
    </script>

</body>
</html>