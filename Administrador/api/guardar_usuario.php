<?php
header('Content-Type: application/json');
include '../../config/db.php';

// Recibir datos del FormData de admin.js
$id       = $_POST['id'] ?? '';
$nombre   = $_POST['nombre'] ?? '';
$correo   = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';
$rol      = $_POST['rol'] ?? '';   // 'alumno' o 'docente'
$extra    = $_POST['extra'] ?? ''; // Matrícula o Especialidad

try {
    $pdo->beginTransaction();

    if (empty($id)) {
        // --- NUEVO REGISTRO ---
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        
        // 1. Insertar en 'usuarios' (solo columnas que existen en esa tabla)
        $sqlU = "INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) VALUES (?, ?, ?, ?, 1)";
        $stmtU = $pdo->prepare($sqlU);
        $stmtU->execute([$nombre, $correo, $passHash, $rol]);
        
        $nuevoId = $pdo->lastInsertId();

        // 2. Insertar en la tabla hija correspondiente
        if ($rol === 'docente') {
            // Según tu DB: tabla 'docentes', columnas: usuario_id, especialidad
            $stmtD = $pdo->prepare("INSERT INTO docentes (usuario_id, especialidad) VALUES (?, ?)");
            $stmtD->execute([$nuevoId, $extra]);
        } else if ($rol === 'alumno') {
            // Según tu DB: tabla 'alumnos', columnas: usuario_id, matricula
            $stmtA = $pdo->prepare("INSERT INTO alumnos (usuario_id, matricula) VALUES (?, ?)");
            $stmtA->execute([$nuevoId, $extra]);
        }
    } else {
        // --- ACTUALIZACIÓN ---
        $stmtU = $pdo->prepare("UPDATE usuarios SET nombre_completo = ?, correo = ? WHERE id = ?");
        $stmtU->execute([$nombre, $correo, $id]);

        if ($rol === 'docente') {
            $stmtD = $pdo->prepare("UPDATE docentes SET especialidad = ? WHERE usuario_id = ?");
            $stmtD->execute([$extra, $id]);
        } else if ($rol === 'alumno') {
            $stmtA = $pdo->prepare("UPDATE alumnos SET matricula = ? WHERE usuario_id = ?");
            $stmtA->execute([$extra, $id]);
        }
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Guardado exitosamente"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>