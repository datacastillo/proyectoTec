<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

// 1. Validar que sea un Docente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    echo json_encode(['success' => false, 'error' => 'Sesión no autorizada']);
    exit;
}

// 2. Recibir los datos del AJAX
$entrega_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$puntos = isset($_POST['puntos']) ? floatval($_POST['puntos']) : 0;

if ($entrega_id > 0) {
    // 3. Actualizar la tabla 'entregas' con tus nombres de columna reales
    // Cambiamos el estatus a 'calificado' automáticamente al poner nota
    $sql = "UPDATE entregas 
            SET puntos_obtenidos = '$puntos', 
                estatus = 'calificado' 
            WHERE id = '$entrega_id'";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de entrega no válido']);
}
?>