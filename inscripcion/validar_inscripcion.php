<?php
// 1. Conexión a la base de datos (Subimos un nivel para llegar a config)
require_once '../config/db.php'; 

// 2. Cabecera para responder en formato JSON
header('Content-Type: application/json');

// 3. Recibir y limpiar datos del formulario
$folio = isset($_POST['folio']) ? trim($_POST['folio']) : '';
$curp  = isset($_POST['curp']) ? strtoupper(trim($_POST['curp'])) : '';

if (empty($folio) || empty($curp)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, ingresa Folio y CURP.']);
    exit;
}

// 4. Consulta Principal: Buscamos al aspirante y el nombre de su carrera
// IMPORTANTE: Solo permite entrar si el estatus en la BD es 'aprobada'
$sql = "SELECT s.nombre, s.apellido, s.carrera_id, c.nombre AS carrera_nombre 
        FROM solicitudes_fichas s 
        JOIN carreras c ON s.carrera_id = c.id 
        WHERE s.folio = ? AND s.curp = ? AND s.estatus = 'aprobada'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ss", $folio, $curp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $id_carrera = $row['carrera_id'];

    // 5. Consulta de Materias: Traemos las materias cargadas para esa carrera
    $sql_m = "SELECT clave, nombre, creditos FROM materias WHERE carrera_id = ?";
    $stmt_m = mysqli_prepare($conexion, $sql_m);
    mysqli_stmt_bind_param($stmt_m, "i", $id_carrera);
    mysqli_stmt_execute($stmt_m);
    $res_m = mysqli_stmt_get_result($stmt_m);
    
    $materias = [];
    while($m = mysqli_fetch_assoc($res_m)) {
        $materias[] = $m;
    }

    // 6. Respuesta de éxito con todos los datos necesarios
    echo json_encode([
        'success' => true,
        'nombre' => $row['nombre'] . " " . $row['apellido'],
        'carrera' => $row['carrera_nombre'],
        'materias' => $materias
    ]);

} else {
    // 7. Respuesta de error si no coincide o no está aprobado aún
    echo json_encode([
        'success' => false, 
        'message' => 'Folio/CURP incorrectos o tu ficha aún no ha sido aprobada por la administración.'
    ]);
}

mysqli_close($conexion);
?>