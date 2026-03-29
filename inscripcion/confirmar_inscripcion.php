<?php
// 1. SILENCIAR ERRORES VISUALES PARA NO ROMPER EL JSON
error_reporting(0); 
ini_set('display_errors', 0);

require_once '../config/db.php'; 
header('Content-Type: application/json');

// Capturador de errores fatales
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
    // Usaremos $pdo para mantener consistencia con el archivo de validación
    
    // 2. BUSCAR ASPIRANTE
    $stmt = $pdo->prepare("SELECT nombre, apellido, curp, carrera_id FROM solicitudes_fichas WHERE folio = ? AND estatus = 'aprobada'");
    $stmt->execute([$folio]);
    $aspirante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aspirante) {
        throw new Exception("El aspirante no fue encontrado o su estatus no es 'aprobada'.");
    }

    // 3. PREPARAR DATOS
    $nombre_completo = $aspirante['nombre'] . " " . $aspirante['apellido'];
    
    // Matrícula: 26 + ID_Carrera + 4 números al azar
    $matricula = "26" . $aspirante['carrera_id'] . str_pad(rand(1, 9999), 4, "0", STR_PAD_LEFT);
    
    // Correo: quitamos espacios por si tiene dos nombres y lo pasamos a minúsculas
    $nombre_sin_espacios = str_replace(' ', '', $aspirante['nombre']);
    $apellido_sin_espacios = str_replace(' ', '', $aspirante['apellido']);
    $correo_oficial = strtolower($nombre_sin_espacios . "." . $apellido_sin_espacios . rand(10,99) . "@tecsanpedro.edu.mx");
    
    // Contraseña: Su CURP encriptada (Requisito estándar de seguridad)
    $password_encriptado = password_hash($aspirante['curp'], PASSWORD_DEFAULT);

    // INICIAMOS TRANSACCIÓN SEGURA (Si falla algo, se deshace todo)
    $pdo->beginTransaction();

    // 4. INSERTAR USUARIO (Tabla principal para el Login)
    $stmt_user = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, 'alumno')");
    $stmt_user->execute([$nombre_completo, $correo_oficial, $password_encriptado]);
    
    $usuario_id = $pdo->lastInsertId();

    // 5. INSERTAR ALUMNO (Tabla ligada a la carrera y matrícula)
    $stmt_alu = $pdo->prepare("INSERT INTO alumnos (usuario_id, carrera_id, matricula) VALUES (?, ?, ?)");
    $stmt_alu->execute([$usuario_id, $aspirante['carrera_id'], $matricula]);

    // 6. ACTUALIZAR FICHA (Para que ya no pueda volver a inscribirse)
    $stmt_update = $pdo->prepare("UPDATE solicitudes_fichas SET estatus = 'inscrito' WHERE folio = ?");
    $stmt_update->execute([$folio]);

    // CONFIRMAMOS TODOS LOS CAMBIOS EN LA BD
    $pdo->commit();

    // REGRESAMOS EL ÉXITO AL JAVASCRIPT
    echo json_encode([
        'success' => true, 
        'num_control' => $matricula,
        'correo' => $correo_oficial // Te lo mando por si quieres sumarlo a tu mensaje de alerta
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); // Si hubo error, deshacemos a medias
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>