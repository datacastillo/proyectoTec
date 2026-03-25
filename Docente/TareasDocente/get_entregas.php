<?php
require_once '../../config/db.php';

$tarea_id = $_GET['tarea_id'];

// 1. Obtener la unidad y grupo de esta tarea para saber qué alumnos listar
$q_info = "SELECT unidad_id FROM tareas WHERE id = '$tarea_id'";
$r_info = mysqli_query($conexion, $q_info);
$tarea_info = mysqli_fetch_assoc($r_info);
$unidad_id = $tarea_info['unidad_id'];

// 2. Traer a los alumnos inscritos en el grupo de esa unidad
// Y hacer un LEFT JOIN con las entregas para ver quién ya subió
$query = "SELECT u.nombre_completo, e.archivo_url, e.fecha_entrega, e.id as entrega_id
          FROM usuarios u
          JOIN alumnos a ON u.id = a.usuario_id
          JOIN inscripciones i ON a.id = i.alumno_id
          JOIN unidades un ON i.grupo_id = un.grupo_id
          LEFT JOIN entregas e ON (e.alumno_id = a.id AND e.tarea_id = '$tarea_id')
          WHERE un.id = '$unidad_id'";

$res = mysqli_query($conexion, $query);

if(mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)) {
        $estatus = $row['entrega_id'] ? "<span style='color:#00ff00;'>ENTREGADO</span>" : "<span style='color:#ff4444;'>PENDIENTE</span>";
        $link = $row['entrega_id'] ? "<a href='../../uploads/tareas/{$row['archivo_url']}' target='_blank' style='color:#0088ff;'>Ver Archivo</a>" : "-";
        
        echo "<tr>
                <td>".htmlspecialchars($row['nombre_completo'])."</td>
                <td>Tarea Unidad</td>
                <td>$link</td>
                <td>$estatus</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center;'>No hay alumnos inscritos en este grupo.</td></tr>";
}
?>