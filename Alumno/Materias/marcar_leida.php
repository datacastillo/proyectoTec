<?php
session_start();
require_once '../../config/db.php'; // Ajusta la ruta a tu archivo de conexión si es necesario

if (isset($_GET['id']) && isset($_SESSION['id_usuario'])) {
    $id_notificacion = mysqli_real_escape_string($conexion, $_GET['id']);
    $id_usuario = $_SESSION['id_usuario'];

    // Actualizamos la notificación a leida=1, verificando que pertenezca a este usuario por seguridad
    $sql = "UPDATE notificaciones SET leido = 1 WHERE id = '$id_notificacion' AND receptor_id = '$id_usuario'";
    
    if (mysqli_query($conexion, $sql)) {
        echo "ok";
    } else {
        echo "error";
    }
}
?>