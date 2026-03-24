<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];

$query_materias = "
    SELECT m.id AS materia_id, m.nombre AS materia_nombre
    FROM materias m
    JOIN grupos g ON m.id = g.materia_id
    JOIN inscripciones i ON g.id = i.grupo_id
    JOIN alumnos a ON i.alumno_id = a.id
    WHERE a.usuario_id = '$id_usuario'
";

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
                <li><a href="../Materias/index.php">MATERIAS ◀</a></li>
                <li><a href="../Tareas/tareas.php">TAREAS ◀</a></li>
                <li><a href="../Kardex/kardex.php">KARDEX ◀</a></li>
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
                            <th>PROM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($res_materias) > 0) {
                            while($materia = mysqli_fetch_assoc($res_materias)) {
                                $m_id = $materia['materia_id'];
                                
                                // Buscamos las notas de esta materia para el alumno
                                // Nota: Asumimos que las unidades 1, 2, 3, 4 existen en tu tabla 'unidades'
                                echo "<tr>";
                                echo "<td class='materia'>" . strtoupper($materia['materia_nombre']) . "</td>";
                                
                                for ($i=1; $i <= 4; $i++) { 
                                    $q_nota = "SELECT cu.nota_final 
                                               FROM calificaciones_unidades cu
                                               JOIN unidades u ON cu.unidad_id = u.id
                                               JOIN grupos g ON u.group_id = g.id
                                               JOIN alumnos a ON cu.alumno_id = a.id
                                               WHERE a.usuario_id = '$id_usuario' 
                                               AND g.materia_id = '$m_id' 
                                               AND u.numero_unit = '$i'";
                                    
                                    $res_nota = mysqli_query($conexion, $q_nota);
                                    $nota = mysqli_fetch_assoc($res_nota);
                                    
                                    echo "<td>" . ($nota ? $nota['nota_final'] : '-') . "</td>";
                                }
                                
                                echo "<td>-</td>"; // Aquí iría el promedio final
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No hay materias inscritas</td></tr>";
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
    </script>

</body>
</html>