<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo "<p style='color: #ffb3b3; text-align: center; padding: 20px;'>⚠️ Selecciona un grupo válido.</p>";
    exit;
}

// =========================================================
// 1. MOSTRAR LAS TAREAS QUE EL DOCENTE HA CREADO
// =========================================================
$sql_tareas = "SELECT t.id, t.titulo, t.descripcion, uni.nombre_unidad 
               FROM tareas t 
               INNER JOIN unidades uni ON t.unidad_id = uni.id 
               WHERE uni.grupo_id = '$grupo_id' 
               ORDER BY t.id DESC";
$res_tareas = mysqli_query($conexion, $sql_tareas);

echo "<h3 style='color: var(--exito); border-bottom: 1px solid var(--accent); padding-bottom: 10px; margin-top: 0;'>📝 Tareas Asignadas a este Grupo</h3>";

if (mysqli_num_rows($res_tareas) == 0) {
    echo "<p style='color: var(--subtexto); margin-bottom: 30px;'>No has creado ninguna tarea para este grupo aún.</p>";
} else {
    echo "<div style='display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 40px;'>";
    while($tarea = mysqli_fetch_assoc($res_tareas)) {
        echo "<div style='background: var(--bg-secundario); padding: 15px; border-radius: 8px; width: 280px; border: 1px solid var(--accent);'>
                <h4 style='margin: 0 0 5px 0; color: white;'>" . htmlspecialchars($tarea['titulo']) . "</h4>
                <small style='color: var(--exito); display: block; margin-bottom: 10px; font-weight: bold;'>" . htmlspecialchars($tarea['nombre_unidad']) . "</small>
                <p style='margin: 0; font-size: 13px; color: var(--subtexto);'>" . htmlspecialchars($tarea['descripcion']) . "</p>
              </div>";
    }
    echo "</div>";
}

// =========================================================
// 2. MOSTRAR LAS ENTREGAS DE LOS ALUMNOS (TU TABLA ORIGINAL)
// =========================================================
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

echo "<h3 style='color: var(--exito); border-bottom: 1px solid var(--accent); padding-bottom: 10px;'>📥 Entregas para Revisar</h3>";

if (mysqli_num_rows($res_e) == 0) {
    echo "<div style='text-align:center; padding:40px; color:var(--subtexto); border:2px dashed var(--accent); border-radius:10px; margin-top:20px;'>
            <p>Ningún alumno ha subido archivos todavía.</p>
          </div>";
    exit;
}

echo '<table style="width:100%; border-collapse:collapse; background:var(--bg-secundario); color:white; border-radius:8px; overflow:hidden;">
        <thead>
            <tr style="background:#1e4d58; border-bottom:2px solid var(--accent);">
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

    echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1); transition: 0.3s;' onmouseover='this.style.background=\"var(--accent)\"' onmouseout='this.style.background=\"transparent\"'>
            <td style='padding:12px 15px;'>
                <b style='font-size:14px;'>" . strtoupper(htmlspecialchars($ent['alumno_nombre'])) . "</b>
            </td>
            <td style='padding:12px 15px;'>
                <span style='color:white; font-weight:bold;'>" . htmlspecialchars($ent['tarea_titulo']) . "</span><br>
                <small style='color:#88d4e8;'>" . htmlspecialchars($ent['nombre_unidad']) . "</small>
            </td>
            <td style='padding:12px 15px; text-align:center;'>
                <span style='background:{$status_color}; color:black; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold;'>
                    {$status_text}
                </span>
            </td>
            <td style='padding:12px 15px; text-align:center;'>
                <button onclick=\"abrirVisor({$ent['entrega_id']}, '" . addslashes($ent['alumno_nombre']) . "', '" . addslashes($ent['tarea_titulo']) . "', '$ruta_pdf', '$nota')\" 
                        style='background:var(--exito); border:none; padding:8px 15px; cursor:pointer; border-radius:5px; font-weight:bold; color:black; transition:0.3s;'>
                    🔍 REVISAR
                </button>
            </td>
          </tr>";
}
echo '</tbody></table>';
?>