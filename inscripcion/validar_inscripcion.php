<?php
include '../config/db.php'; 

// Esto es para que el navegador sepa que vamos a responder con datos (JSON)
header('Content-Type: application/json');

// Recibimos los datos que envió el JavaScript
$folio = $_POST['folio'] ?? '';
$curp  = $_POST['curp'] ?? '';

if (empty($folio) || empty($curp)) {
    echo json_encode(['success' => false, 'message' => 'Campos vacíos']);
    exit;
}

// Hacemos un JOIN para traer el nombre de la carrera de la otra tabla
$sql = "SELECT s.nombre, s.apellido, s.carrera_id, c.nombre AS carrera_nombre 
        FROM solicitudes_fichas s 
        JOIN carreras c ON s.carrera_id = c.id 
        WHERE s.folio = '$folio' AND s.curp = '$curp' AND s.estatus = 'aprobada'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_carrera = $row['carrera_id'];

    // Buscamos las materias que insertamos para esa carrera_id
    $sql_m = "SELECT clave, nombre, creditos FROM materias WHERE carrera_id = $id_carrera";
    $res_m = $conn->query($sql_m);
    
    $materias = [];
    while($m = $res_m->fetch_assoc()) {
        $materias[] = $m;
    }

    // Si todo sale bien, mandamos success: true y los datos
    echo json_encode([
        'success' => true,
        'nombre' => $row['nombre'] . " " . $row['apellido'],
        'carrera' => $row['carrera_nombre'],
        'materias' => $materias
    ]);

} else {
    // Si no lo encuentra o no está aprobado
    echo json_encode([
        'success' => false, 
        'message' => 'Aspirante no encontrado o no ha sido aprobado.'
    ]);
}

$conn->close();
?>