<?php
session_start();
header('Content-Type: application/json');

// Ajusta la ruta a tu conexión
include '../../config/db.php';

// Validar que solo el administrador pueda ver esto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    // Consultamos la tabla solicitudes_fichas
    $query = "SELECT id, folio, nombre, apellido, correo, carrera_id, estatus, fecha_solicitud 
              FROM solicitudes_fichas 
              ORDER BY fecha_solicitud DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $fichas = [];

    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Juntamos el nombre y el apellido para el render de admin.js
        $fila['nombre_completo'] = $fila['nombre'] . ' ' . $fila['apellido'];
        $fichas[] = $fila;
    }

    echo json_encode($fichas);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>