<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_usuario'])) {
    die("Acceso denegado.");
}

$id_emisor = $_SESSION['id_usuario']; 
$id_receptor = mysqli_real_escape_string($conexion, $_GET['id_usuario']); 
$mensaje = "SISTEMA: Tu promedio actual es de riesgo (menor a 70). Favor de regularizar tus actividades.";

// Usando los nombres de tus columnas: emisor_id, receptor_id, mensaje, leido, fecha_envio
$sql = "INSERT INTO notificaciones (emisor_id, receptor_id, mensaje, leido, fecha_envio) 
        VALUES ('$id_emisor', '$id_receptor', '$mensaje', 0, NOW())";

if (mysqli_query($conexion, $sql)) {
    echo "success";
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>