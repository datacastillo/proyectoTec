<?php
// Configuración de la base de datos
$host     = "localhost";
$user     = "root";     
$password = "";          
$dbname   = "control_escolar";

// conexión
$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// caracteres a UTF-8 
$conn->set_charset("utf8");
?>