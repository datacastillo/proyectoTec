<?php
// 1. Errores desactivados para producción, pero con log interno
error_reporting(0);
ini_set('display_errors', 0);

// 2. Ruta de conexión (Asegurando que suba un nivel a Administrador y luego a config)
$ruta_db = '../config/db.php'; 
if (!file_exists($ruta_db)) {
    $ruta_db = '../../config/db.php'; 
}

require_once $ruta_db;
header('Content-Type: application/json');

// 3. Capturar datos sincronizados con admin.js
// admin.js envía 'id' y 'estatus'
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$estado = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';

if ($id === 0 || empty($estado)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Faltan datos en la petición (ID: '.$id.', Estado: '.$estado.')'
    ]);
    exit;
}

try {
    // 4. Ejecutar la actualización usando la conexión del admin ($conexion)
    $query = "UPDATE solicitudes_fichas SET estatus = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, "si", $estado, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => "La ficha ha sido marcada como " . strtoupper($estado)
        ]);
    } else {
        throw new Exception("No se pudo actualizar el registro.");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>