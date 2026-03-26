<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;
$id_usuario = $_SESSION['id_usuario'] ?? 0;

// Obtener ID del docente real
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// Consultar grupos de la materia y el docente
$query = "SELECT id, nombre_grupo FROM grupos 
          WHERE materia_id = '$materia_id' AND docente_id = '$id_docente'";
$res = mysqli_query($conexion, $query);

$grupos = [];
while($row = mysqli_fetch_assoc($res)) {
    $grupos[] = [
        'id' => $row['id'],
        'nombre_grupo' => $row['nombre_grupo']
    ];
}
echo json_encode($grupos);