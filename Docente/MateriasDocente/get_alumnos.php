<?php
require_once '../../config/db.php';

if (isset($_GET['grupo_id'])) {
    $id_grupo = mysqli_real_escape_string($conexion, $_GET['grupo_id']);

    // Usando 'nota_final' que es el nombre real en tu tabla
    $query = "SELECT a.id AS id_alumno, a.matricula, u.nombre_completo, u.id AS id_usuario_alumno,
              IFNULL(AVG(cu.nota_final), 0) as promedio
              FROM inscripciones i
              INNER JOIN alumnos a ON i.alumno_id = a.id
              INNER JOIN usuarios u ON a.usuario_id = u.id
              LEFT JOIN calificaciones_unidades cu ON a.id = cu.alumno_id
              WHERE i.grupo_id = '$id_grupo'
              GROUP BY a.id, a.matricula, u.nombre_completo, u.id
              ORDER BY u.nombre_completo ASC";

    $res = mysqli_query($conexion, $query);

    if (!$res) {
        die("Error en SQL: " . mysqli_error($conexion));
    }

    if (mysqli_num_rows($res) > 0) {
        echo '<table class="tabla-alumnos">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre del Alumno</th>
                        <th>Promedio</th>
                        <th>Estatus</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = mysqli_fetch_assoc($res)) {
            $prom = round($row['promedio'], 1);
            
            // Lógica de colores (Semáforo)
            if ($prom == 0) { 
                $badge = 'badge-gris'; $status = 'Sin Notas'; 
            } elseif ($prom < 70) { 
                $badge = 'badge-rojo'; $status = 'En Riesgo'; 
            } elseif ($prom < 85) { 
                $badge = 'badge-amarillo'; $status = 'Regular'; 
            } else { 
                $badge = 'badge-verde'; $status = 'Aprobado'; 
            }

            echo "<tr>
                    <td>{$row['matricula']}</td>
                    <td style='text-align:left;'>".htmlspecialchars($row['nombre_completo'])."</td>
                    <td><strong>$prom</strong></td>
                    <td><span class='badge $badge'>$status</span></td>
                    <td>";
            
            if ($prom < 70 && $prom > 0) {
                $nombreJS = addslashes($row['nombre_completo']);
                echo "<button onclick='enviarAlerta({$row['id_usuario_alumno']}, \"$nombreJS\")' class='btn-alerta'>⚠️ Avisar</button>";
            } else {
                echo "<small style='color:#adb5bd;'>Al día</small>";
            }
            echo "</td></tr>";
        }
        echo '</tbody></table>';
    } else {
        echo "<p style='text-align:center; color:white; padding:20px;'>No hay alumnos registrados en este grupo.</p>";
    }
}
?>