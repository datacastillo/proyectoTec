<?php
include '../config/db.php';

// Indicamos que la respuesta será JSON
header('Content-Type: application/json');

// Recibimos los datos enviados por JavaScript
$folio = $_POST['folio'] ?? '';
$curp = $_POST['curp'] ?? '';

if (empty($folio) || empty($curp)) {
    echo json_encode(['success' => false, 'message' => 'Campos vacíos']);
    exit;
}

// Hacemos un JOIN para obtener el nombre de la carrera
$sql = "SELECT s.nombre, s.apellido, s.carrera_id, c.nombre AS carrera_nombre 
        FROM solicitudes_fichas s 
        JOIN carreras c ON s.carrera_id = c.id 
        WHERE s.folio = ? AND s.curp = ? AND s.estatus = 'aprobada'";

//$result = $conn->query($sql);
$stmt = $pdo->prepare($sql);
$stmt->execute([$folio, $curp]);
$row = $stmt->fetch();

if ($row) {
    $id_carrera = $row['carrera_id'];

    // Obtenemos las materias que incertamos para esa caarrera
    $sql_m = "SELECT clave, nombre, creditos FROM materias WHERE carrera_id = ?";
    $stmt_m = $pdo->prepare($sql_m);
    $stmt_m->execute([$id_carrera]);
    $materias = $stmt_m->fetchAll();

    echo json_encode([
        'success' => true,
        'nombre' => $row['nombre'] . " " . $row['apellido'],
        'carrera' => $row['carrera_nombre'],
        'materias' => $materias
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aspirante no encontrado o no ha sido aprobado.'
    ]);
}
?>