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
    // 2. BUSCAR ASPIRANTE
    $stmt = $pdo->prepare("SELECT nombre, apellido, curp, carrera_id FROM solicitudes_fichas WHERE folio = ? AND estatus = 'aprobada'");
    $stmt->execute([$folio]);
    $aspirante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aspirante) {
        throw new Exception("El aspirante no fue encontrado o su estatus no es 'aprobada'.");
    }

    // 3. PREPARAR DATOS
    $nombre_completo = $aspirante['nombre'] . " " . $aspirante['apellido'];
    
    $matricula = "26" . $aspirante['carrera_id'] . str_pad(rand(1, 9999), 4, "0", STR_PAD_LEFT);
    
    $nombre_sin_espacios = str_replace(' ', '', $aspirante['nombre']);
    $apellido_sin_espacios = str_replace(' ', '', $aspirante['apellido']);
    $correo_oficial = strtolower($nombre_sin_espacios . "." . $apellido_sin_espacios . rand(10,99) . "@tecsanpedro.edu.mx");
    
    $password_encriptado = password_hash($aspirante['curp'], PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    // 4. INSERTAR USUARIO (Tabla principal para login)
    $stmt_user = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol) VALUES (?, ?, ?, 'alumno')");
    $stmt_user->execute([$nombre_completo, $correo_oficial, $password_encriptado]);
    
    $usuario_id = $pdo->lastInsertId();

    // 5. INSERTAR ALUMNO (Generamos su perfil académico)
    $stmt_alu = $pdo->prepare("INSERT INTO alumnos (usuario_id, carrera_id, matricula) VALUES (?, ?, ?)");
    $stmt_alu->execute([$usuario_id, $aspirante['carrera_id'], $matricula]);
    
    // ¡AQUÍ ESTÁ LA CLAVE! Obtenemos el ID del alumno que acabamos de crear en la línea anterior
    $alumno_id = $pdo->lastInsertId(); 

    // 6. ASIGNAR CARGA ACADÉMICA (Primer Semestre)
    // Buscamos los grupos vinculados a materias de su carrera y que sean de 1er semestre
    $stmt_grupos = $pdo->prepare("
        SELECT g.id AS grupo_id 
        FROM grupos g
        INNER JOIN materias m ON g.materia_id = m.id
        WHERE m.carrera_id = ? AND g.semestre = 1
    ");
    $stmt_grupos->execute([$aspirante['carrera_id']]);
    $grupos_disponibles = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

    // Si encontramos grupos para su carrera, lo inscribimos en la tabla "inscripciones"
    if (!empty($grupos_disponibles)) {
        $stmt_inscripcion = $pdo->prepare("INSERT INTO inscripciones (alumno_id, grupo_id) VALUES (?, ?)");
        foreach ($grupos_disponibles as $grupo) {
            $stmt_inscripcion->execute([$alumno_id, $grupo['grupo_id']]);
        }
    }

    // 7. ACTUALIZAR FICHA (Cerramos el proceso del aspirante)
    $stmt_update = $pdo->prepare("UPDATE solicitudes_fichas SET estatus = 'inscrito' WHERE folio = ?");
    $stmt_update->execute([$folio]);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'num_control' => $matricula,
        'correo' => $correo_oficial
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>