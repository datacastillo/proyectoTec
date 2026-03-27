<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

// SOLUCIÓN: Agregué GROUP BY a.id al final para evitar duplicados como el de Nahomi
$query = "SELECT a.matricula, u.nombre_completo, u.correo 
          FROM inscripciones i
          INNER JOIN alumnos a ON i.alumno_id = a.id
          INNER JOIN usuarios u ON a.usuario_id = u.id
          WHERE i.grupo_id = '$grupo_id'
          GROUP BY a.id
          ORDER BY u.nombre_completo ASC";

$res = mysqli_query($conexion, $query);

if (!$res) {
    die("<p style='color:#ffb3b3;'>Error en base de datos: " . mysqli_error($conexion) . "</p>");
}

if (mysqli_num_rows($res) > 0) {
    echo '<table style="width:100%; border-collapse:collapse; background:#255b68; color:white; border-radius:8px; overflow:hidden;">
            <thead>
                <tr style="background:#1e4d58; border-bottom:2px solid #2b6671;">
                    <th style="padding:15px; text-align:left;">MATRÍCULA</th>
                    <th style="padding:15px; text-align:left;">NOMBRE DEL ALUMNO</th>
                    <th style="padding:15px; text-align:left;">CORREO ELECTRÓNICO</th>
                </tr>
            </thead>
            <tbody>';
    
    while($row = mysqli_fetch_assoc($res)) {
        echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1);' onmouseover='this.style.background=\"#2b6671\"' onmouseout='this.style.background=\"none\"'>
                <td style='padding:12px 15px; font-family:monospace; color:#88d4e8;'>{$row['matricula']}</td>
                <td style='padding:12px 15px;'><b>".strtoupper($row['nombre_completo'])."</b></td>
                <td style='padding:12px 15px; color:#adb5bd;'>{$row['correo']}</td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo "<div style='background:#1e4d58; color:#adb5bd; text-align:center; padding:40px; border-radius:10px; border:1px solid #2b6671;'>
            <p style='font-size:18px; margin:0;'>👥 Sin alumnos</p>
            <p style='font-size:14px; margin-top:5px;'>No hay alumnos inscritos en este grupo todavía.</p>
          </div>";
}
?>