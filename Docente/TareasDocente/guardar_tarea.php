<?php
session_start();
require_once '../../config/db.php';

// Verificamos si la petición es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recibimos los datos del formulario (el grupo_id también se envía pero la tarea se enlaza a la unidad)
    $titulo = mysqli_real_escape_string($conexion, trim($_POST['titulo'] ?? ''));
    $unidad_id = mysqli_real_escape_string($conexion, $_POST['unidad_id'] ?? '');
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion'] ?? ''));

    // Validamos que los campos obligatorios no estén vacíos
    if (empty($titulo) || empty($unidad_id)) {
        echo "Faltan datos obligatorios (Título o Unidad).";
        exit;
    }

    // Insertamos la tarea en la base de datos
    // Nota: Si tu tabla 'tareas' tiene otros campos obligatorios (como 'fecha_limite'), 
    // debes agregarlos aquí. Por ahora asumo la estructura básica.
    $sql = "INSERT INTO tareas (unidad_id, titulo, descripcion) 
            VALUES ('$unidad_id', '$titulo', '$descripcion')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "success";
    } else {
        // Si hay error en la tabla, lo imprimimos para que salga en la alerta de JS
        echo "Error MySQL: " . mysqli_error($conexion);
    }
} else {
    echo "Método no permitido.";
}
?>