<?php

require_once '../config/db.php'; 

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validar que los datos existan en el POST antes de asignarlos
    $nombre     = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $apellido   = isset($_POST['apellido']) ? $_POST['apellido'] : '';
    $curp       = isset($_POST['curp']) ? strtoupper($_POST['curp']) : '';
    $correo     = isset($_POST['correo']) ? strtolower($_POST['correo']) : '';
    $carrera_id = isset($_POST['carrera_id']) ? intval($_POST['carrera_id']) : 0;

    // Verificar conexión a la base de datos
    if (!$conexion) {
        echo json_encode(['status' => 'error', 'message' => 'Fallo la conexión a la BD']);
        exit;
    }

    // Generar Folio Automático (TEC-2026-XXX)
    $sql_count = "SELECT COUNT(*) as total FROM solicitudes_fichas";
    $res_count = mysqli_query($conexion, $sql_count);
    
    if ($res_count) {
        $row_count = mysqli_fetch_assoc($res_count);
        $siguiente = $row_count['total'] + 1;
    } else {
        $siguiente = 1; // Si la tabla está vacía o hay error inicial
    }
    
    $folio = "TEC-2026-" . str_pad($siguiente, 3, "0", STR_PAD_LEFT);

    //  Preparar el INSERT
    $sql_insert = "INSERT INTO solicitudes_fichas (folio, nombre, apellido, curp, correo, carrera_id, estatus) 
                   VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = mysqli_prepare($conexion, $sql_insert);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssi", $folio, $nombre, $apellido, $curp, $correo, $carrera_id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success',
                'folio' => $folio
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al ejecutar: ' . mysqli_stmt_error($stmt)
            ]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en la preparación: ' . mysqli_error($conexion)
        ]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>