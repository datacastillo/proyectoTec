<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente' || !isset($_GET['id_grupo'])) {
    die("Acceso denegado o grupo no especificado.");
}

$id_grupo = mysqli_real_escape_string($conexion, $_GET['id_grupo']);

// 1. Obtener información: 
// g.nombre_grupo (Tabla grupos)
// m.nombre (Tabla materias) - Corregido aquí
// u.nombre_completo (Tabla usuarios)
$query_info = "SELECT g.nombre_grupo, m.nombre AS materia_nombre, u.nombre_completo AS docente_nombre 
               FROM grupos g
               INNER JOIN materias m ON g.materia_id = m.id
               INNER JOIN docentes d ON g.docente_id = d.id
               INNER JOIN usuarios u ON d.usuario_id = u.id
               WHERE g.id = '$id_grupo' LIMIT 1";

$res_info = mysqli_query($conexion, $query_info);

if(!$res_info) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

if(mysqli_num_rows($res_info) == 0) {
    die("Grupo no encontrado.");
}

$info = mysqli_fetch_assoc($res_info);

// 2. Obtener Alumnos
$query_alumnos = "SELECT a.matricula, u.nombre_completo 
                  FROM inscripciones i
                  INNER JOIN alumnos a ON i.alumno_id = a.id
                  INNER JOIN usuarios u ON a.usuario_id = u.id
                  WHERE i.grupo_id = '$id_grupo' 
                  ORDER BY u.nombre_completo ASC";

$res_alumnos = mysqli_query($conexion, $query_alumnos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia - <?php echo htmlspecialchars($info['materia_nombre']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #555; padding: 20px; margin: 0; }
        .hoja { background: white; width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.5cm; box-shadow: 0 0 10px rgba(0,0,0,0.5); box-sizing: border-box; }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header img { max-width: 120px; }
        .info-grupo { text-align: right; font-size: 13px; color: #000; line-height: 1.4; }
        .info-grupo h2 { margin: 0 0 5px 0; font-size: 18px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; font-size: 10px; }
        th { background-color: #f2f2f2; }
        .nombre-alumno { text-align: left; padding-left: 10px; }

        .btn-imprimir {
            position: fixed; top: 20px; right: 20px; background: #2ecc71; color: black; 
            padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;
        }

        @media print {
            body { background: white; padding: 0; }
            .hoja { box-shadow: none; width: 100%; margin: 0; padding: 0; }
            .btn-imprimir { display: none; }
            @page { size: portrait; margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="btn-imprimir" onclick="window.print()">🖨️ IMPRIMIR LISTA</button>

    <div class="hoja">
        <div class="header">
            <img src="../../imagen/teclogo.png" alt="Logo">
            <div class="info-grupo">
                <h2>LISTA DE ASISTENCIA</h2>
                <div><b>Materia:</b> <?php echo htmlspecialchars($info['materia_nombre']); ?></div>
                <div><b>Grupo:</b> <?php echo htmlspecialchars($info['nombre_grupo']); ?></div>
                <div><b>Docente:</b> <?php echo htmlspecialchars($info['docente_nombre']); ?></div>
                <div><b>Fecha:</b> <?php echo date("d/m/Y"); ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">N°</th>
                    <th style="width: 80px;">Matrícula</th>
                    <th>Nombre del Alumno</th>
                    <?php for($i=1; $i<=15; $i++) echo "<th style='width:20px;'>$i</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $num = 1;
                while($alumno = mysqli_fetch_assoc($res_alumnos)): 
                ?>
                <tr>
                    <td><?php echo $num++; ?></td>
                    <td><?php echo htmlspecialchars($alumno['matricula']); ?></td>
                    <td class="nombre-alumno"><?php echo htmlspecialchars($alumno['nombre_completo']); ?></td>
                    <?php for($i=1; $i<=15; $i++) echo "<td></td>"; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>