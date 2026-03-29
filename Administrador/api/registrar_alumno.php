<?php
header('Content-Type: application/json');
include '../../config/db.php';

// Recibir datos del formulario (Asegúrate que los names coincidan con tu HTML)
$nombre    = $_POST['nombre_completo'] ?? '';
$correo    = $_POST['correo'] ?? '';
$password  = $_POST['password'] ?? '123456'; 
$matricula = $_POST['matricula'] ?? '';
$carrera_id = $_POST['carrera_id'] ?? null; // Si usas IDs de carrera

// VALIDACIÓN CRÍTICA
if (empty($matricula)) {
    echo json_encode(["success" => false, "message" => "La matrícula es obligatoria."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar en tabla usuarios (Rol: alumno)
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtU = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) VALUES (?, ?, ?, 'alumno', 1)");
    $stmtU->execute([$nombre, $correo, $passHash]);
    
    $usuario_id = $pdo->lastInsertId();

    // 2. Insertar en tabla alumnos
    // Ajusta los nombres de las columnas según tu base de datos (ej. matricula, carrera_id)
    $stmtA = $pdo->prepare("INSERT INTO alumnos (usuario_id, matricula, carrera_id) VALUES (?, ?, ?)");
    $stmtA->execute([$usuario_id, $matricula, $carrera_id]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Alumno registrado con éxito"]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    
    // Error 23000: Entrada duplicada (Matrícula o Correo)
    if ($e->getCode() == 23000) {
        echo json_encode(["success" => false, "message" => "Error: La matrícula '$matricula' o el correo ya están registrados."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error DB: " . $e->getMessage()]);
    }
}
?>