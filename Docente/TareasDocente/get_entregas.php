<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo "<p style='color: #ffb3b3; text-align: center; padding: 20px;'>⚠️ Selecciona un grupo válido.</p>";
    exit;
}

// Consulta optimizada: Trae entregas, datos del alumno y de la tarea en un solo viaje
$sql_entregas = "SELECT 
                    e.id AS entrega_id, 
                    e.archivo_alumno, 
                    e.puntos_obtenidos, 
                    u_alu.nombre_completo AS alumno_nombre,
                    t.titulo AS tarea_titulo,
                    uni.nombre_unidad
                 FROM entregas e
                 INNER JOIN tareas t ON e.tarea_id = t.id
                 INNER JOIN unidades uni ON t.unidad_id = uni.id
                 INNER JOIN alumnos a ON e.alumno_id = a.id
                 INNER JOIN usuarios u_alu ON a.usuario_id = u_alu.id
                 WHERE uni.grupo_id = '$grupo_id'
                 ORDER BY t.id DESC, u_alu.nombre_completo ASC";

$res_e = mysqli_query($conexion, $sql_entregas);

if (mysqli_num_rows($res_e) == 0) {
    echo "<div style='text-align:center; padding:50px; color:#adb5bd; border:2px dashed #2b6671; border-radius:10px; margin:20px;'>
            <p>No hay entregas registradas en este grupo todavía.</p>
          </div>";
    exit;
}

echo '<table style="width:100%; border-collapse:collapse; background:#255b68; color:white; border-radius:8px; overflow:hidden;">
        <thead>
            <tr style="background:#1e4d58; border-bottom:2px solid #2b6671;">
                <th style="padding:15px; text-align:left;">ALUMNO</th>
                <th style="padding:15px; text-align:left;">TAREA / UNIDAD</th>
                <th style="padding:15px; text-align:center;">ESTADO</th>
                <th style="padding:15px; text-align:center;">ACCIÓN</th>
            </tr>
        </thead>
        <tbody>';

while ($ent = mysqli_fetch_assoc($res_e)) {
    // IMPORTANTE: Verifica que esta ruta sea la correcta hacia tus archivos PDF
    $ruta_pdf = "../../Alumno/Tareas/uploads/" . $ent['archivo_alumno'];
    $nota = $ent['puntos_obtenidos'];
    
    // Semáforo de estado
    $status_color = ($nota > 0) ? '#2ecc71' : '#f1c40f';
    $status_text = ($nota > 0) ? "Calificado: $nota" : "Pendiente";

    echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1);' onmouseover='this.style.background=\"#2b6671\"' onmouseout='this.style.background=\"none\"'>
            <td style='padding:12px 15px;'>
                <b style='font-size:14px;'>" . strtoupper($ent['alumno_nombre']) . "</b>
            </td>
            <td style='padding:12px 15px;'>
                <span style='color:white; font-weight:bold;'>{$ent['tarea_titulo']}</span><br>
                <small style='color:#88d4e8;'>{$ent['nombre_unidad']}</small>
            </td>
            <td style='padding:12px 15px; text-align:center;'>
                <span style='background:{$status_color}; color:black; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:bold;'>
                    {$status_text}
                </span>
            </td>
            <td style='padding:12px 15px; text-align:center;'>
                <button onclick=\"abrirVisor({$ent['entrega_id']}, '" . addslashes($ent['alumno_nombre']) . "', '" . addslashes($ent['tarea_titulo']) . "', '$ruta_pdf', '$nota')\" 
                        style='background:#2ecc71; border:none; padding:8px 15px; cursor:pointer; border-radius:5px; font-weight:bold; color:black; transition:0.3s;'>
                    🔍 REVISAR
                </button>
            </td>
          </tr>";
}
echo '</tbody></table>';
?>