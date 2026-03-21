<?php
// Configuración de la base de datos
$host = "localhost";
$user = "admin";
$password = "Qwerty1234";
$dbname = "control_escolar";
$charset = 'utf8mb4';

/*
// conexión
$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// caracteres a UTF-8 
$conn->set_charset("utf8");

*/

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    // echo "Conexión exitosa :D"; 
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
?>