<?php
session_start();
require_once '../../config/db.php';

// Verificamos sesión por seguridad
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
    exit();
}

// Recibimos los datos del formulario
$unidad_id  = $_POST['unidad_id'] ?? 0;
$titulo     = mysqli_real_escape_escape_string($conexion, $_POST['titulo'] ?? '');
$descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');

if ($unidad_id > 0 && !empty($titulo)) {
    // Insertamos la tarea vinculándola a la unidad seleccionada
    // Asegúrate de que tu tabla 'tareas' tenga estas columnas: titulo, descripcion, unidad_id
    $sql = "INSERT INTO tareas (titulo, descripcion, unidad_id, fecha_creacion) 
            VALUES ('$titulo', '$descripcion', '$unidad_id', NOW())";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos (Título o Unidad faltante)']);
}
?>