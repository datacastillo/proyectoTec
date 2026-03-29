<?php
include '../../config/db.php';

$docente_id = $_POST['docente_id']; // El ID del usuario docente
$materia_id = $_POST['materia_id'];
$nombre_grupo = $_POST['nombre_grupo']; // Ejemplo: 'A'
$semestre = $_POST['semestre'];
$ciclo = $_POST['ciclo_escolar']; // Ejemplo: '2026-1'

try {
    $sql = "INSERT INTO grupos (materia_id, docente_id, nombre_grupo, semestre, ciclo_escolar) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$materia_id, $docente_id, $nombre_grupo, $semestre, $ciclo]);

    echo json_encode(["success" => true, "message" => "Materia asignada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>