<?php
// 1. Asegurar que no haya espacios antes de esta etiqueta
session_start();

// 2. DEBUG: Si esto se ejecuta, veremos qué tiene la sesión antes de que te eche
if (!isset($_SESSION['rol'])) {
    echo "<h1>Error de Sesión</h1>";
    echo "La sesión está vacía. <br>";
    echo "ID de Sesión: " . session_id() . "<br>";
    echo "<pre>Contenido de SESSION:"; 
    print_r($_SESSION);
    echo "</pre>";
    echo "<a href='../../auth/login.html'>Ir al Login manualmente</a>";
    exit(); // Detenemos aquí para que no te redirija automáticamente
}

require_once '../../config/db.php';

// Validar que sea docente
if ($_SESSION['rol'] !== 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario']; 
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';
// ... resto del código