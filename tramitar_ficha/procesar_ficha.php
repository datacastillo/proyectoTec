<?php
// Forzar visualización de errores para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir y limpiar datos
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $curp       = strtoupper(trim($_POST['curp'] ?? ''));
    $correo     = strtolower(trim($_POST['correo'] ?? ''));
    $carrera_id = intval($_POST['carrera_id'] ?? 0);

    // Validación básica de campos obligatorios para la BD
    if (empty($nombre) || empty($apellido) || empty($curp) || $carrera_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    // Generar Folio
    $res_count = mysqli_query($conexion, "SELECT COUNT(*) as total FROM solicitudes_fichas");
    $row_count = mysqli_fetch_assoc($res_count);
    $folio = "TEC-2026-" . str_pad(($row_count['total'] + 1), 3, "0", STR_PAD_LEFT);

    // Insertar con el campo 'estatus' que por defecto es 'pendiente'
    $sql = "INSERT INTO solicitudes_fichas (folio, nombre, apellido, curp, correo, carrera_id, estatus) 
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = mysqli_prepare($conexion, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssi", $folio, $nombre, $apellido, $curp, $correo, $carrera_id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'folio' => $folio]);
        } else {
            // Esto devolverá el error específico de SQL (ej: columna duplicada)
            echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en preparación: ' . mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>