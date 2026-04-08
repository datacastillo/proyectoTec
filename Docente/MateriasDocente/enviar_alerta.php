<?php
session_start();
require_once '../../config/db.php';

// Validar sesión y datos necesarios
if (!isset($_SESSION['id_usuario']) || !isset($_GET['id_usuario'])) {
    exit("Acceso denegado.");
}

$id_emisor = $_SESSION['id_usuario']; 
$id_receptor = $_GET['id_usuario']; 

// Recibimos la materia por GET para hacer el mensaje específico
$materia = isset($_GET['materia']) ? $_GET['materia'] : "una de tus materias";
$mensaje = "SISTEMA: Tu promedio actual en $materia es de riesgo (menor a 70). Favor de regularizar tus actividades.";

// USANDO CONSULTAS PREPARADAS (Seguridad anti-inyecciones)
$sql = "INSERT INTO notificaciones (emisor_id, receptor_id, mensaje, leido, fecha_envio) VALUES (?, ?, ?, 0, NOW())";
$stmt = mysqli_prepare($conexion, $sql);

if ($stmt) {
    // "iis" significa: integer, integer, string
    mysqli_stmt_bind_param($stmt, "iis", $id_emisor, $id_receptor, $mensaje);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "Error al ejecutar: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error de preparación: " . mysqli_error($conexion);
}
?>