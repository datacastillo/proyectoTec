<?php
require_once '../../config/db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $fecha = $_POST['fecha'];
    $unidad_id = $_POST['unidad_id'];

    // Insertamos la tarea vinculada a la unidad
    $query = "INSERT INTO tareas (unidad_id, titulo, descripcion, fecha_limite, fecha_creacion) 
              VALUES ('$unidad_id', '$titulo', '$descripcion', '$fecha', NOW())";

    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
?>