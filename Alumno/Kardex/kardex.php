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

// Obtenemos el ID real del alumno
$query_alu = "SELECT id FROM alumnos WHERE usuario_id = '$id_usuario'";
$res_alu = mysqli_query($conexion, $query_alu);
$reg_alu = mysqli_fetch_assoc($res_alu);
$alumno_id = $reg_alu['id'];

// CONSULTA DE MATERIAS (DISTINCT para evitar duplicados en la tabla)
$query_kardex = "
    SELECT DISTINCT m.id AS materia_id, m.nombre, m.clave 
    FROM materias m
    JOIN grupos g ON m.id = g.materia_id
    JOIN inscripciones i ON g.id = i.grupo_id
    WHERE i.alumno_id = '$alumno_id'";

$res_kardex = mysqli_query($conexion, $query_kardex);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex | Tec San Pedro</title>
    <link rel="stylesheet" href="../Materias/styles.css">
    <link rel="stylesheet" href="kardex.css">
    <style>
        .promedio-final { font-weight: bold; color: #00ff00; }
        .materia-row td { padding: 15px; border: 1px solid rgba(255,255,255,0.2); }
    </style>
</head>
<body style="background-color: #0d2c44; color: white; font-family: sans-serif;">

    <div style="text-align: center; padding: 40px;">
        <h1 style="font-size: 3rem; margin-bottom: 30px;">Kardex de: <?php echo $nombreAlumno; ?></h1>

        <div id="areaDescarga" style="display: inline-block; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px;">
            <table style="width: 800px; border-collapse: collapse; text-align: center;">
                <thead>
                    <tr style="background-color: rgba(255,255,255,0.1);">
                        <th style="padding: 15px;">MATERIA</th>
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
                            $suma = 0;
                            $cont = 0;
                            
                            echo "<tr class='materia-row'>";
                            echo "<td style='text-align: left;'><strong>" . $row['clave'] . "</strong> - " . $row['nombre'] . "</td>";
                            
                            for ($i=1; $i <= 4; $i++) { 
                                // Consulta corregida con grupo_id y numero_unit
                                $q_n = "SELECT cu.nota_final 
                                       FROM calificaciones_unidades cu
                                       JOIN unidades u ON cu.unidad_id = u.id
                                       JOIN grupos g ON u.grupo_id = g.id
                                       WHERE cu.alumno_id = '$alumno_id' 
                                       AND g.materia_id = '$m_id' 
                                       AND u.numero_unit = '$i'";
                                
                                $r_n = mysqli_query($conexion, $q_n);
                                $n = mysqli_fetch_assoc($r_n);
                                
                                if ($n) {
                                    $nota = (float)$n['nota_final'];
                                    $suma += $nota;
                                    $cont++;
                                    echo "<td>" . number_format($nota, 2) . "</td>";
                                } else {
                                    echo "<td>-</td>";
                                }
                            }
                            
                            $prom = ($cont > 0) ? round($suma / $cont, 0) : 0;
                            echo "<td class='promedio-final'>" . ($prom > 0 ? $prom : '-') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay materias registradas</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 30px;">
            <button id="btnDescargar" style="background: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                DESCARGAR PDF
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        document.getElementById("btnDescargar").addEventListener("click", function() {
            const { jsPDF } = window.jspdf;
            let area = document.getElementById("areaDescarga");
            
            html2canvas(area, { backgroundColor: "#0d2c44", scale: 2 }).then(canvas => {
                let imgData = canvas.toDataURL("image/png");
                let pdf = new jsPDF('l', 'px', [canvas.width, canvas.height]);
                pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
                pdf.save("Kardex_<?php echo $nombreAlumno; ?>.pdf");
            });
        });
    </script>
</body>
</html>