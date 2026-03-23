<?php
$host = "localhost";
$user = "root"; 
$password = ""; 
$dbname = "control_escolar";

// Conexión  (MySQLi)
$conexion = mysqli_connect($host, $user, $password, $dbname);
if (!$conexion) { die("Error MySQLi: " . mysqli_connect_error()); }
mysqli_set_charset($conexion, "utf8");

// Conexión  (PDO)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Error silencioso
}
?>