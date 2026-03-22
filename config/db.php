<?php
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "control_escolar";

// Salida archivos (MySQLi)
$conexion = mysqli_connect($host, $user, $password, $dbname);
mysqli_set_charset($conexion, "utf8");

// Salida archivos de (PDO)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Error silencioso para no romper mysqli
}
?>