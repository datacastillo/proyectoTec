<?php
require_once '../../config/db.php';

// Limpiar el buffer de salida para evitar que cualquier espacio en blanco o error previo rompa el JSON
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$grupo_id = $_GET['grupo_id'] ?? 0;

if (!$grupo_id) {
    echo json_encode(['error' => 'Grupo no especificado']);
    exit;
}

// Asegurarse de que el ID sea seguro (evitar inyección SQL básica)
$grupo_id = mysqli_real_escape_string($conexion, $grupo_id);

$sql = "SELECT id, nombre_unidad FROM unidades WHERE grupo_id = '$grupo_id' ORDER BY id ASC";
$res = mysqli_query($conexion, $sql);

if (!$res) {
     echo json_encode(['error' => 'Error en la base de datos: ' . mysqli_error($conexion)]);
     exit;
}

$unidades = [];
while ($u = mysqli_fetch_assoc($res)) {
    $unidades[] = [
        'id' => $u['id'],
        'nombre_unidad' => $u['nombre_unidad']
    ];
}

echo json_encode($unidades);
?>