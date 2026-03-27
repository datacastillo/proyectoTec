<?php
require_once '../../config/db.php';

// Recibimos los datos por POST desde el Visor
$entrega_id = isset($_POST['entrega_id']) ? intval($_POST['entrega_id']) : 0;
$nota = isset($_POST['nota']) ? mysqli_real_escape_string($conexion, $_POST['nota']) : 0;

if ($entrega_id > 0) {
    // Actualizamos la columna puntos_obtenidos (basado en tu captura de get_entregas.php)
    $sql = "UPDATE entregas SET puntos_obtenidos = '$nota' WHERE id = '$entrega_id'";
    
    if (mysqli_query($conexion, $sql)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conexion);
    }
} else {
    echo "invalid_id";
}
?>