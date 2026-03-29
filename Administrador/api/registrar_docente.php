<?php
header('Content-Type: application/json');
include '../../config/db.php';

// Recibir datos del formulario
$nombre       = $_POST['nombre_completo'] ?? '';
$correo       = $_POST['correo'] ?? '';
$password     = $_POST['password'] ?? '123456'; // Password por defecto si no hay
$especialidad = $_POST['especialidad'] ?? '';
$num_empleado = $_POST['numero_empleado'] ?? ''; 

// VALIDACIÓN CRÍTICA: Si el número de empleado llega vacío, dará error de duplicado
if (empty($num_empleado)) {
    echo json_encode(["success" => false, "message" => "El número de empleado es obligatorio para evitar errores de duplicidad."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar en usuarios
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtU = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) VALUES (?, ?, ?, 'docente', 1)");
    $stmtU->execute([$nombre, $correo, $passHash]);
    
    $usuario_id = $pdo->lastInsertId();

    // 2. Insertar en docentes
    $stmtD = $pdo->prepare("INSERT INTO docentes (usuario_id, numero_empleado, especialidad) VALUES (?, ?, ?)");
    $stmtD->execute([$usuario_id, $num_empleado, $especialidad]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Docente registrado con éxito"]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    
    // Si el error es el 23000 (Duplicate entry)
    if ($e->getCode() == 23000) {
        echo json_encode(["success" => false, "message" => "Error: El número de empleado '$num_empleado' o el correo ya existen."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error DB: " . $e->getMessage()]);
    }
}
?>