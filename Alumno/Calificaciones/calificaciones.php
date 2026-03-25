<?php
session_start();
require_once '../../config/db.php';

// Verificación de sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];

// CONSULTA DE MATERIAS: Basada en la carrera del alumno
$query_materias = "
    SELECT m.id AS materia_id, m.nombre AS materia_nombre
    FROM materias m
    INNER JOIN alumnos a ON m.carrera_id = a.carrera_id
    WHERE a.usuario_id = '$id_usuario'
";
$res_materias = mysqli_query($conexion, $query_materias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones | Tec San Pedro</title>
    <link rel="stylesheet" href="calificaciones.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo" alt="Logo Tec">

        <div class="top-actions">
            <a href="../../auth/logout.php" class="home" title="Cerrar Sesión">🚪</a>
            <div class="notificacion-container">
                🔔 <span id="notif-dot" class="notif-dot"></span>
            </div>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <div class="user">
                <img src="../img/user.png" class="user-img" alt="Usuario" onerror="this.src='https://ui-avatars.com/api/?name=User'">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>

            <ul class="menu">
                <li class="activo">CALIFICACIONES ▼</li>
                <li><a href="../Materias/index.php" style="color:white; text-decoration:none; display:block; padding:10px;">MATERIAS ◀</a></li>
                <li><a href="../Tareas/tareas.php" style="color:white; text-decoration:none; display:block; padding:10px;">TAREAS ◀</a></li>
                <li><a href="../Kardex/kardex.php" style="color:white; text-decoration:none; display:block; padding:10px;">KARDEX ◀</a></li>
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
                        if($res_materias && mysqli_num_rows($res_materias) > 0) {
                            while($materia = mysqli_fetch_assoc($res_materias)) {
                                $m_id = $materia['materia_id'];
                                echo "<tr>";
                                echo "<td class='materia'>" . strtoupper($materia['materia_nombre']) . "</td>";
                                
                                $suma = 0;
                                $cont = 0;

                                // Consultamos las 4 unidades. 
                                // Nota: u.grupo_id conecta con materias a través de la tabla grupos
                                for ($i=1; $i <= 4; $i++) { 
                                    $q_nota = "SELECT cu.nota_final 
                                               FROM calificaciones_unidades cu
                                               INNER JOIN unidades u ON cu.unidad_id = u.id
                                               INNER JOIN grupos g ON u.grupo_id = g.id
                                               INNER JOIN alumnos a ON cu.alumno_id = a.id
                                               WHERE a.usuario_id = '$id_usuario' 
                                               AND g.materia_id = '$m_id' 
                                               AND u.numero_unit = '$i'";
                                    
                                    $res_nota = mysqli_query($conexion, $q_nota);
                                    $nota_data = mysqli_fetch_assoc($res_nota);
                                    
                                    $valor = ($nota_data && $nota_data['nota_final'] !== null) ? $nota_data['nota_final'] : '-';
                                    
                                    echo "<td>" . $valor . "</td>";
                                    
                                    if(is_numeric($valor)) {
                                        $suma += $valor;
                                        $cont++;
                                    }
                                }
                                
                                $promedio = ($cont > 0) ? round($suma / $cont, 1) : '-';
                                echo "<td style='font-weight:bold; color:#0044cc;'>$promedio</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No se encontraron materias vinculadas a tu carrera.</td></tr>";
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