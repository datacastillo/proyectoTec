<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

$alumno_id = $_POST['alumno_id'] ?? 0;
$unidad_id = $_POST['unidad_id'] ?? 0;
$nota = $_POST['nota'] ?? 0;

// INSERT con UPDATE por si ya existe el registro (Upsert)
$sql = "INSERT INTO calificaciones_unidades (alumno_id, unidad_id, nota_final) 
        VALUES ('$alumno_id', '$unidad_id', '$nota') 
        ON DUPLICATE KEY UPDATE nota_final = '$nota'";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
}
?>