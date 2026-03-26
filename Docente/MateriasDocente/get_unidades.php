<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;

if ($materia_id > 0) {
    // Buscamos unidades a través de la tabla grupos
    $query = "SELECT u.id, u.numero_unit, u.nombre_unidad 
              FROM unidades u
              JOIN grupos g ON u.group_id = g.id 
              WHERE g.materia_id = '$materia_id' 
              ORDER BY u.numero_unit ASC";
              
    $result = mysqli_query($conexion, $query);

    if (!$result) {
        echo json_encode(['error' => mysqli_error($conexion)]);
        exit;
    }

    $unidades = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $unidades[] = [
            'id' => $row['id'],
            'nombre' => "U" . $row['numero_unit'] . " - " . $row['nombre_unidad']
        ];
    }
    echo json_encode($unidades);
} else {
    echo json_encode([]);
}