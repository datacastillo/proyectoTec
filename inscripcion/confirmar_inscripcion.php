<?php
// 1. SILENCIAR ERRORES VISUALES PARA NO ROMPER EL JSON
error_reporting(0); 
ini_set('display_errors', 0);

require_once '../config/db.php'; 
header('Content-Type: application/json');

// Capturador de errores fatales para enviarlos como JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        echo json_encode(['success' => false, 'message' => 'Error Fatal PHP: ' . $error['message']]);
    }
});

$folio = $_POST['folio'] ?? '';

if (empty($folio)) {
    echo json_encode(['success' => false, 'message' => 'Folio vacío']);
    exit;
}

try {
    // 2. BUSCAR ASPIRANTE
    $query = "SELECT nombre, apellido, curp, carrera_id FROM solicitudes_fichas WHERE folio = ? AND estatus = 'aprobada'";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "s", $folio);
    mysqli_stmt_execute($stmt);
    $aspirante = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$aspirante) {
        throw new Exception("Aspirante no encontrado o estatus no es 'aprobada'");
    }

    // 3. PREPARAR DATOS
    $nombre_completo = $aspirante['nombre'] . " " . $aspirante['apellido'];
    $matricula = "26" . $aspirante['carrera_id'] . str_pad(rand(1, 9999), 4, "0", STR_PAD_LEFT);
    $correo_oficial = strtolower($aspirante['nombre'] . "." . $aspirante['apellido'] . rand(10,99) . "@tecsanpedro.edu.mx");
    $password_encriptado = password_hash($aspirante['curp'], PASSWORD_DEFAULT);

    mysqli_begin_transaction($conexion);

    // 4. INSERTAR USUARIO
    $sql_user = "INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, 'alumno')";
    $stmt_user = mysqli_prepare($conexion, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "sss", $nombre_completo, $correo_oficial, $password_encriptado);
    
    if (!mysqli_stmt_execute($stmt_user)) {
        throw new Exception("Error al crear usuario: " . mysqli_error($conexion));
    }
    
    $usuario_id = mysqli_insert_id($conexion);

    // 5. INSERTAR ALUMNO
    $sql_alu = "INSERT INTO alumnos (usuario_id, carrera_id, matricula) VALUES (?, ?, ?)";
    $stmt_alu = mysqli_prepare($conexion, $sql_alu);
    mysqli_stmt_bind_param($stmt_alu, "iis", $usuario_id, $aspirante['carrera_id'], $matricula);
    
    if (!mysqli_stmt_execute($stmt_alu)) {
        throw new Exception("Error al crear alumno: " . mysqli_error($conexion));
    }

    // 6. ACTUALIZAR FICHA
    mysqli_query($conexion, "UPDATE solicitudes_fichas SET estatus = 'inscrito' WHERE folio = '$folio'");

    mysqli_commit($conexion);

    echo json_encode(['success' => true, 'num_control' => $matricula]);

} catch (Exception $e) {
    if (isset($conexion)) mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>