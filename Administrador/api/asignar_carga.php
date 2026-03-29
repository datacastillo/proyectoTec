<?php
header('Content-Type: application/json');
include '../../config/db.php';

// Validar que los datos existan para evitar errores de "Undefined index"
if (empty($_POST['materia_id']) || empty($_POST['docente_id']) || empty($_POST['nombre_grupo'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit;
}

$materia_id   = $_POST['materia_id'];
$docente_id   = $_POST['docente_id']; 
$nombre_grupo = $_POST['nombre_grupo'];
$semestre     = $_POST['semestre'];
$ciclo        = $_POST['ciclo_escolar'];

try {
    // Verificamos si ya existe ese grupo exacto para no duplicar carga
    $check = $pdo->prepare("SELECT id FROM grupos WHERE materia_id = ? AND docente_id = ? AND nombre_grupo = ? AND ciclo_escolar = ?");
    $check->execute([$materia_id, $docente_id, $nombre_grupo, $ciclo]);
    
    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Este docente ya tiene asignada esta materia en ese grupo"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO grupos (materia_id, docente_id, nombre_grupo, semestre, ciclo_escolar) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$materia_id, $docente_id, $nombre_grupo, $semestre, $ciclo]);

    echo json_encode(["success" => true, "message" => "Carga asignada correctamente"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error DB: " . $e->getMessage()]);
}
?>