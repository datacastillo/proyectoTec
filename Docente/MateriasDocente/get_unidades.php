<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo "Selecciona un grupo válido.";
    exit;
}

// 1. Obtener las unidades de este grupo
$sql_unidades = "SELECT id, nombre_unidad FROM unidades WHERE grupo_id = '$grupo_id' ORDER BY id ASC";
$res_unidades = mysqli_query($conexion, $sql_unidades);
$unidades = [];
while ($u = mysqli_fetch_assoc($res_unidades)) {
    $unidades[] = $u;
}

// 2. Obtener los alumnos del grupo
$sql_alumnos = "SELECT a.id AS alumno_id, u.nombre_completo 
                FROM alumnos a 
                JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.id IN (SELECT alumno_id FROM alumno_grupo WHERE grupo_id = '$grupo_id')
                ORDER BY u.nombre_completo ASC";
$res_alumnos = mysqli_query($conexion, $sql_alumnos);

if (mysqli_num_rows($res_alumnos) == 0) {
    echo "<p style='text-align:center; padding:20px;'>No hay alumnos inscritos en este grupo.</p>";
    exit;
}

// Renderizar Tabla
echo '<table style="width:100%; border-collapse:collapse; color:white; background:var(--bg-secundario); border-radius:10px; overflow:hidden;">
        <thead>
            <tr style="background:var(--accent); text-align:left;">
                <th style="padding:15px;">ALUMNO</th>';
foreach ($unidades as $uni) {
    echo "<th style='padding:15px; text-align:center;'>{$uni['nombre_unidad']}</th>";
}
echo '          <th style="padding:15px; text-align:center;">ACCIÓN</th>
            </tr>
        </thead>
        <tbody>';

while ($alu = mysqli_fetch_assoc($res_alumnos)) {
    echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1);'>";
    echo "<td style='padding:12px 15px; font-weight:bold;'>" . strtoupper($alu['nombre_completo']) . "</td>";

    $notas_alumno = []; // Para el JSON del modal

    foreach ($unidades as $uni) {
        $a_id = $alu['alumno_id'];
        $u_id = $uni['id'];
        
        $sql_nota = "SELECT calificacion FROM calificaciones WHERE alumno_id = '$a_id' AND unidad_id = '$u_id'";
        $res_nota = mysqli_query($conexion, $sql_nota);
        $dato_nota = mysqli_fetch_assoc($res_nota);
        $valor_nota = $dato_nota['calificacion'] ?? 0;

        // Guardamos para el modal
        $notas_alumno[] = [
            'unidad_id' => $u_id,
            'nombre' => $uni['nombre_unidad'],
            'nota' => $valor_nota
        ];

        $color = ($valor_nota >= 70) ? 'var(--exito)' : '#ff4d4d';
        echo "<td style='text-align:center; color:$color; font-weight:bold;'>$valor_nota</td>";
    }

    // AQUÍ ESTÁ EL TRUCO: rawurlencode para que el JSON no rompa el JS
    $json_seguro = rawurlencode(json_encode($notas_alumno));
    $nom_alu = addslashes($alu['nombre_completo']);

    echo "<td style='text-align:center; padding:10px;'>
            <button onclick=\"abrirModalGraduar('{$alu['alumno_id']}', '$nom_alu', '$json_seguro')\" 
                    style='background:var(--exito); border:none; color:black; padding:8px 12px; border-radius:5px; cursor:pointer; font-weight:bold;'>
                📝 CALIFICAR
            </button>
          </td>";
    echo "</tr>";
}
echo '</tbody></table>';
?>