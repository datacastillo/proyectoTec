<?php
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo "<p style='color:white; text-align:center;'>Selecciona un grupo válido.</p>";
    exit;
}

// 1. Obtener las unidades del grupo
$sql_unidades = "SELECT id, nombre_unidad FROM unidades WHERE grupo_id = '$grupo_id' ORDER BY id ASC";
$res_unidades = mysqli_query($conexion, $sql_unidades);
$unidades = [];
while ($u = mysqli_fetch_assoc($res_unidades)) {
    $unidades[] = $u;
}

// 2. Obtener los alumnos inscritos (¡AQUÍ CORREGIMOS EL NOMBRE DE LA TABLA A "inscripciones"!)
$sql_alumnos = "SELECT a.id AS alumno_id, u.nombre_completo, a.matricula 
                FROM alumnos a 
                JOIN usuarios u ON a.usuario_id = u.id 
                WHERE a.id IN (SELECT alumno_id FROM inscripciones WHERE grupo_id = '$grupo_id')
                ORDER BY u.nombre_completo ASC";
$res_alumnos = mysqli_query($conexion, $sql_alumnos);

if (!$res_alumnos) {
    echo "<p style='color:red; text-align:center;'>Error en la consulta de alumnos: " . mysqli_error($conexion) . "</p>";
    exit;
}

if (mysqli_num_rows($res_alumnos) == 0) {
    echo "<p style='color:white; text-align:center; padding: 20px;'>No hay alumnos inscritos en este grupo.</p>";
    exit;
}

echo '<table style="width:100%; border-collapse:collapse; color:white; background:#1a3c4d; border-radius:10px; overflow:hidden;">
        <thead>
            <tr style="background:#255b68;">
                <th style="padding:15px; text-align:left;">ALUMNO</th>';
foreach ($unidades as $uni) {
    echo "<th style='padding:15px; text-align:center; font-size:12px;'>" . strtoupper($uni['nombre_unidad']) . "</th>";
}
echo '          <th style="padding:15px; text-align:center;">ACCIONES</th>
            </tr>
        </thead>
        <tbody>';

while ($alu = mysqli_fetch_assoc($res_alumnos)) {
    echo "<tr style='border-bottom:1px solid rgba(255,255,255,0.1);'>";
    echo "<td style='padding:12px 15px;'>
            <div style='font-weight:bold;'>" . strtoupper($alu['nombre_completo']) . "</div>
            <div style='font-size:11px; color:#adb5bd;'>" . $alu['matricula'] . "</div>
          </td>";

    $notas_para_modal = [];

    foreach ($unidades as $uni) {
        $a_id = $alu['alumno_id'];
        $u_id = $uni['id'];
        
        // Consulta a la tabla correcta de calificaciones
        $sql_nota = "SELECT nota_final FROM calificaciones_unidades WHERE alumno_id = '$a_id' AND unidad_id = '$u_id'";
        $res_nota = mysqli_query($conexion, $sql_nota);
        $dato_nota = mysqli_fetch_assoc($res_nota);
        $valor_nota = $dato_nota['nota_final'] ?? 0;

        $notas_para_modal[] = [
            'unidad_id' => $u_id,
            'nombre' => $uni['nombre_unidad'],
            'nota' => $valor_nota
        ];

        $color = ($valor_nota >= 70) ? '#2ecc71' : '#ff4d4d';
        echo "<td style='text-align:center; color:$color; font-weight:bold;'>$valor_nota</td>";
    }

    // Empaquetado seguro para el JSON
    $json_seguro = rawurlencode(json_encode($notas_para_modal));
    $nombre_escapado = addslashes($alu['nombre_completo']);

    echo "<td style='text-align:center;'>
            <button onclick=\"abrirModalGraduar('{$alu['alumno_id']}', '$nombre_escapado', '$json_seguro')\" 
                    style='background:none; border:1px solid #2ecc71; color:#2ecc71; padding:5px 10px; border-radius:5px; cursor:pointer;'>
                📝 Calificar
            </button>
          </td>";
    echo "</tr>";
}
echo '</tbody></table>';
?>