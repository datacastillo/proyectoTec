<?php
header('Content-Type: application/json');
include '../../config/db.php';

// Recibir datos del formulario
$nombre     = $_POST['nombre_completo'] ?? '';
$correo     = $_POST['correo'] ?? '';
$password   = $_POST['password'] ?? '123456'; 
$matricula  = $_POST['matricula'] ?? '';
$carrera_id = $_POST['carrera_id'] ?? null;
$grupo_id   = $_POST['grupo_id'] ?? null; // <-- Recibimos el ID del grupo desde el modal

// VALIDACIÓN
if (empty($matricula) || empty($grupo_id)) {
    echo json_encode(["success" => false, "message" => "La matrícula y el grupo son obligatorios."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar en tabla usuarios
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtU = $pdo->prepare("INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) VALUES (?, ?, ?, 'alumno', 1)");
    $stmtU->execute([$nombre, $correo, $passHash]);
    $usuario_id = $pdo->lastInsertId();

    // 2. Insertar en tabla alumnos
    $stmtA = $pdo->prepare("INSERT INTO alumnos (usuario_id, matricula, carrera_id) VALUES (?, ?, ?)");
    $stmtA->execute([$usuario_id, $matricula, $carrera_id]);
    $alumno_id = $pdo->lastInsertId(); // Obtenemos el ID del alumno recién creado

    // 3. Vincular alumno con TODA LA CARGA ACADÉMICA del grupo
    // A) Obtenemos los detalles del grupo seleccionado (Nombre, semestre y ciclo)
    $stmtInfo = $pdo->prepare("SELECT nombre_grupo, semestre, ciclo_escolar FROM grupos WHERE id = ?");
    $stmtInfo->execute([$grupo_id]);
    $infoGrupo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if ($infoGrupo) {
        // B) Buscamos TODAS las filas en 'grupos' que pertenezcan a este mismo bloque (ej. todas las del 1° A)
        $stmtTodas = $pdo->prepare("SELECT id FROM grupos WHERE nombre_grupo = ? AND semestre = ? AND ciclo_escolar = ?");
        $stmtTodas->execute([
            $infoGrupo['nombre_grupo'], 
            $infoGrupo['semestre'], 
            $infoGrupo['ciclo_escolar']
        ]);
        $todasLasMaterias = $stmtTodas->fetchAll(PDO::FETCH_ASSOC);

        // C) Inscribimos al alumno en todas esas materias usando un ciclo
        $stmtI = $pdo->prepare("INSERT INTO inscripciones (alumno_id, grupo_id, fecha_inscripcion) VALUES (?, ?, NOW())");
        foreach ($todasLasMaterias as $materia) {
            $stmtI->execute([$alumno_id, $materia['id']]);
        }
    } else {
        // Fallback: Por si acaso algo falla en la búsqueda, insertamos al menos el ID que llegó
        $stmtI = $pdo->prepare("INSERT INTO inscripciones (alumno_id, grupo_id, fecha_inscripcion) VALUES (?, ?, NOW())");
        $stmtI->execute([$alumno_id, $grupo_id]);
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Alumno registrado e inscrito con éxito en todas sus materias"]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    
    if ($e->getCode() == 23000) {
        echo json_encode(["success" => false, "message" => "Error: Matrícula o correo duplicados."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error DB: " . $e->getMessage()]);
    }
}
?>