<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo "<p style='text-align:center; color:#adb5bd; padding:40px;'>Selecciona un grupo válido.</p>";
    exit;
}

// 1. Obtener las unidades de este grupo
$unidades_query = "SELECT id, nombre_unidad FROM unidades WHERE grupo_id = '$grupo_id' ORDER BY id ASC";
$res_unidades = mysqli_query($conexion, $unidades_query);
$unidades = [];
while($u = mysqli_fetch_assoc($res_unidades)) {
    $unidades[] = $u;
}

// 2. Obtener los alumnos inscritos
$alumnos_query = "SELECT a.id as alumno_id, a.matricula, u.nombre_completo 
                  FROM inscripciones i
                  INNER JOIN alumnos a ON i.alumno_id = a.id
                  INNER JOIN usuarios u ON a.usuario_id = u.id
                  WHERE i.grupo_id = '$grupo_id'
                  GROUP BY a.id
                  ORDER BY u.nombre_completo ASC";
$res_alumnos = mysqli_query($conexion, $alumnos_query);

if (count($unidades) == 0) {
    echo "<div style='background:#1e4d58; padding:20px; border-radius:10px; text-align:center; border:1px solid #2b6671;'>
            <p style='color:#ffb3b3; margin:0;'>⚠️ No hay unidades creadas para este grupo.</p>
            <p style='font-size:13px; color:#adb5bd;'>Debes crear unidades antes de poder asignar calificaciones finales.</p>
          </div>";
    exit;
}

echo '<table style="width:100%; border-collapse:collapse; background:#255b68; color:white; border-radius:8px; overflow:hidden;">
        <thead>
            <tr style="background:#1e4d58; border-bottom:2px solid #2b6671;">
                <th style="padding:15px; text-align:left;">ALUMNO</th>';

// Cabeceras de Unidades
foreach ($unidades as $uni) {
    echo "<th style='padding:15px; text-align:center; font-size:13px;'>".strtoupper($uni['nombre_unidad'])."</th>";
}

// Cabecera de Promedio y la Nueva Cabecera de ACCIONES
echo '          <th style="padding:15px; text-align:center; background:rgba(0,0,0,0.2);">PROMEDIO</th>
                <th style="padding:15px; text-align:center;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>';

while($alu = mysqli_fetch_assoc($res_alumnos)) {
    $a_id = $alu['alumno_id'];
    echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1);' onmouseover='this.style.background=\"#2b6671\"' onmouseout='this.style.background=\"none\"'>
            <td style='padding:12px 15px;'>
                <b style='font-size:14px;'>".strtoupper($alu['nombre_completo'])."</b><br>
                <small style='color:#88d4e8;'>{$alu['matricula']}</small>
            </td>";
    
    $suma = 0;
    $notas_alumno = []; // Arreglo para guardar las notas y mandarlas al modal
    
    // Iteramos unidades
    foreach ($unidades as $uni) {
        $u_id = $uni['id'];
        
        $nota_query = "SELECT nota_final FROM calificaciones_unidades WHERE alumno_id = '$a_id' AND unidad_id = '$u_id'";
        $res_nota = mysqli_query($conexion, $nota_query);
        $nota_data = mysqli_fetch_assoc($res_nota);
        $nota = $nota_data['nota_final'] ?? 0;
        $suma += $nota;

        // Guardamos el ID, nombre de la unidad y calificación actual en el arreglo
        $notas_alumno[] = [
            'unidad_id' => $u_id,
            'nombre' => $uni['nombre_unidad'],
            'nota' => $nota
        ];

        // Visualización: Nota estática, color rojo si reprobó (<70)
        $nota_color = ($nota < 70) ? '#ff4d4d' : 'white';
        echo "<td style='padding:12px; text-align:center; font-weight:bold; color:{$nota_color};'>{$nota}</td>";
    }
    
    // Promedio
    $promedio = ($suma > 0) ? ($suma / count($unidades)) : 0;
    $color_promedio = ($promedio < 70) ? '#ff4d4d' : '#2ecc71';

    echo "<td style='padding:12px; text-align:center; font-weight:bold; color:{$color_promedio}; background:rgba(0,0,0,0.1);'>
            ".number_format($promedio, 1)."
          </td>";
    
    // Convertimos el arreglo a texto JSON y lo hacemos seguro para HTML
    $notas_json = json_encode($notas_alumno);
    $notas_json_seguro = htmlspecialchars($notas_json, ENT_QUOTES, 'UTF-8');
    $nombre_seguro = htmlspecialchars($alu['nombre_completo'], ENT_QUOTES, 'UTF-8');

    // Botón con los TRES parámetros que necesita el JavaScript
    echo "<td style='padding:12px; text-align:center;'>
            <button onclick=\"abrirModalGraduar({$a_id}, '{$nombre_seguro}', '{$notas_json_seguro}')\"
                    style='background: #2b6671; color:white; border:1px solid #adb5bd; padding: 8px 15px; border-radius: 5px; cursor:pointer; font-weight:bold; font-size:12px; transition:0.3s;'
                    onmouseover='this.style.background=\"#2ecc71\"; this.style.borderColor=\"#2ecc71\"'
                    onmouseout='this.style.background=\"#2b6671\"; this.style.borderColor=\"#adb5bd\"'>
                ✏️ Calificar
            </button>
          </td>
          </tr>";
}
echo '</tbody></table>';
?>