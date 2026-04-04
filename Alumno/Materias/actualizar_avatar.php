<?php
session_start();
require_once '../../config/db.php';

// Validar que un alumno haya iniciado sesión
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'alumno') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
// Evitar inyección SQL
$nuevo_avatar = mysqli_real_escape_string($conexion, $_POST['avatar']);

// Actualizar el avatar en la base de datos
$query = "UPDATE usuarios SET avatar = '$nuevo_avatar' WHERE id = '$id_usuario'";

if (mysqli_query($conexion, $query)) {
    echo json_encode(['success' => true, 'message' => 'Avatar actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
}
?>