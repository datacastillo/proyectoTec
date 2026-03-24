<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$matricula = $_SESSION['matricula'];
$nombreAlumno = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Alumno';

$query_kardex = "
    SELECT m.id AS materia_id, m.nombre, m.clave 
    FROM materias m
    JOIN grupos g ON m.id = g.materia_id
    JOIN inscripciones i ON g.id = i.grupo_id
    JOIN alumnos a ON i.alumno_id = a.id
    WHERE a.usuario_id = '$id_usuario'";

$res_kardex = mysqli_query($conexion, $query_kardex);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex | Tec San Pedro</title>
    <link rel="stylesheet" href="../Materias/styles.css">
    <link rel="stylesheet" href="kardex.css">
</head>

<body>

    <header class="topbar">
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <img src="../img/logoTec.png" class="logo">

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
                <img src="../img/user.png" class="user-img">
                <span class="user-id"><?php echo $matricula; ?></span>
            </div>

            <ul class="menu">
                <li class="activo">KARDEX ▼</li>
                <li><a href="../Materias/index.php">MATERIAS ◀</a></li>
                <li><a href="../Calificaciones/calificaciones.php">CALIFICACIONES ◀</a></li>
                <li><a href="../Tareas/tareas.php">TAREAS ◀</a></li>
            </ul>
        </aside>

        <main class="contenido">
            <div class="contenedor-tabla">
                <h2 style="margin-bottom: 10px; color: #333;">Kardex de: <?php echo $nombreAlumno; ?></h2>
                <div class="tabla-scroll">
                    <table id="tablaKardex" class="tabla-kardex">
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
                            if(mysqli_num_rows($res_kardex) > 0) {
                                while($row = mysqli_fetch_assoc($res_kardex)) {
                                    $m_id = $row['materia_id'];
                                    echo "<tr>";
                                    echo "<td class='grupo'><strong>" . strtoupper($row['clave']) . "</strong> - " . strtoupper($row['nombre']) . "</td>";
                                    
                                    // Buscar las calificaciones de cada unidad
                                    for ($i=1; $i <= 4; $i++) { 
                                        $q_n = "SELECT cu.nota_final 
                                               FROM calificaciones_unidades cu
                                               JOIN unidades u ON cu.unidad_id = u.id
                                               JOIN grupos g ON u.group_id = g.id
                                               JOIN alumnos a ON cu.alumno_id = a.id
                                               WHERE a.usuario_id = '$id_usuario' 
                                               AND g.materia_id = '$m_id' 
                                               AND u.numero_unit = '$i'";
                                        
                                        $r_n = mysqli_query($conexion, $q_n);
                                        $n = mysqli_fetch_assoc($r_n);
                                        echo "<td>" . ($n ? $n['nota_final'] : '-') . "</td>";
                                    }
                                    
                                    echo "<td>-</td>"; // Espacio para el promedio final
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>Sin registros académicos</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <button id="btnDescargar" class="btn-descargar">DESCARGAR PDF</button>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        function toggleMenu() {
            document.querySelector(".sidebar").classList.toggle("active");
        }

        const { jsPDF } = window.jspdf;
        document.getElementById("btnDescargar").addEventListener("click", function() {
            let tabla = document.getElementById("tablaKardex");
            html2canvas(tabla, { scale: 2 }).then(canvas => {
                let img = canvas.toDataURL("image/png");
                let pdf = new jsPDF({
                    orientation: "landscape",
                    unit: "px",
                    format: [canvas.width, canvas.height]
                });
                pdf.addImage(img, "PNG", 0, 0, canvas.width, canvas.height);
                pdf.save("kardex_<?php echo $matricula; ?>.pdf");
            });
        });
    </script>

</body>
</html>