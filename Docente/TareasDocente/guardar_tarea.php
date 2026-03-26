<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar sesión
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        exit;
    }

    // Recibir datos (ajustados a tu formulario)
    $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $fecha_limite = $_POST['fecha'];
    $unidad_id = intval($_POST['unidad_id']);
    
    // IMPORTANTE: Tu tabla 'tareas' pide puntos_maximos. 
    // Como no está en el modal, le pondremos 100 por defecto o puedes agregarlo al HTML.
    $puntos = 100; 

    $sql = "INSERT INTO tareas (unidad_id, titulo, descripcion, puntos_maximos, fecha_entrega_limite) 
            VALUES ('$unidad_id', '$titulo', '$descripcion', '$puntos', '$fecha_limite')";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
?>