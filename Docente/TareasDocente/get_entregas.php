<?php
session_start();
require_once '../../config/db.php';

$tarea_id = $_GET['tarea_id'] ?? 0;

if (!$tarea_id) {
    echo "<p style='color:white;'>Error: Tarea no identificada.</p>";
    exit;
}

// Consulta usando tus nombres reales: 'entregas', 'archivo_alumno', 'puntos_obtenidos'
$query = "SELECT e.id, a.matricula, u.nombre_completo, e.archivo_alumno, e.fecha_subida, e.puntos_obtenidos 
          FROM entregas e
          JOIN alumnos a ON e.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          WHERE e.tarea_id = '$tarea_id'";

$res = mysqli_query($conexion, $query);

if (!$res) {
    die("<p style='color:red;'>Error en consulta: " . mysqli_error($conexion) . "</p>");
}

if (mysqli_num_rows($res) > 0) {
    echo '<table style="width:100%; color:white; border-collapse:collapse; margin-top:10px;">
            <thead>
                <tr style="border-bottom:2px solid #3e92cc; color:#3e92cc;">
                    <th style="padding:10px; text-align:left;">Alumno</th>
                    <th style="padding:10px; text-align:center;">Archivo</th>
                    <th style="padding:10px; text-align:center;">Calificación</th>
                    <th style="padding:10px; text-align:center;">Acción</th>
                </tr>
            </thead>
            <tbody>';
    while($row = mysqli_fetch_assoc($res)) {
        // Mostramos los puntos_obtenidos actuales
        $nota_actual = $row['puntos_obtenidos'] ?? '';
        
        echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.05);'>
                <td style='padding:10px;'>".strtoupper($row['nombre_completo'])."<br><small style='color:#666;'>{$row['matricula']}</small></td>
                <td style='padding:10px; text-align:center;'>
                    <a href='../../uploads/tareas/{$row['archivo_alumno']}' target='_blank' style='text-decoration:none; color:#3e92cc;'>📂 Descargar</a>
                </td>
                <td style='padding:10px; text-align:center;'>
                    <input type='number' id='puntos_{$row['id']}' value='{$nota_actual}' min='0' max='100' style='width:60px; background:#0d1b2a; border:1px solid #3e92cc; color:white; text-align:center; padding:5px; border-radius:4px;'>
                </td>
                <td style='padding:10px; text-align:center;'>
                    <button onclick='guardarPuntosTarea({$row['id']})' style='background:#28a745; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;'>Guardar</button>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo "<p style='color:#adb5bd; text-align:center; padding:30px;'>Aún no hay archivos entregados para esta tarea.</p>";
}