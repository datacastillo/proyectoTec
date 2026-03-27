<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

// Recibimos los datos del FormData enviado por el JS
$alumno_id = isset($_POST['alumno_id']) ? intval($_POST['alumno_id']) : 0;
$unidad_id = isset($_POST['unidad_id']) ? intval($_POST['unidad_id']) : 0;
$nota = isset($_POST['nota']) ? mysqli_real_escape_string($conexion, $_POST['nota']) : 0;

if ($alumno_id > 0 && $unidad_id > 0) {
    // Usamos el nombre exacto de tu tabla y columna: nota_final
    $sql = "INSERT INTO calificaciones_unidades (alumno_id, unidad_id, nota_final) 
            VALUES ('$alumno_id', '$unidad_id', '$nota') 
            ON DUPLICATE KEY UPDATE nota_final = '$nota'";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Faltan IDs de alumno o unidad']);
}
?>