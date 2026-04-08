<?php
session_start();
require_once '../../config/db.php';

// Limpiamos la salida para evitar espacios en blanco extra
ob_clean();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $grupo_id = mysqli_real_escape_string($conexion, $_POST['grupo_id'] ?? '');
    $nombre_unidad = mysqli_real_escape_string($conexion, trim($_POST['nombre_unidad'] ?? ''));

    if (empty($grupo_id) || empty($nombre_unidad)) {
        echo "Faltan datos (Grupo o Nombre de la unidad).";
        exit;
    }

    // --- VALIDACIÓN DE LÍMITE DE 6 UNIDADES ---
    $query_check = "SELECT COUNT(*) as total FROM unidades WHERE grupo_id = '$grupo_id'";
    $res_check = mysqli_query($conexion, $query_check);
    $data_check = mysqli_fetch_assoc($res_check);

    if ($data_check['total'] >= 6) {
        // Enviamos un mensaje específico que el JS pueda leer
        echo "limite_alcanzado"; 
        exit;
    }
    // ------------------------------------------

    // ¡AQUÍ ESTÁ LA SOLUCIÓN! 
    // Calculamos qué número de unidad le toca automáticamente
    $numero_unit = $data_check['total'] + 1;

    // Insertamos la unidad guardando también el 'numero_unit'
    $sql = "INSERT INTO unidades (grupo_id, nombre_unidad, numero_unit) VALUES ('$grupo_id', '$nombre_unidad', '$numero_unit')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "success";
    } else {
        echo "Error MySQL: " . mysqli_error($conexion);
    }
} else {
    echo "Método no permitido.";
}
?>