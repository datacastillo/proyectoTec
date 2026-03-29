<?php
header('Content-Type: application/json');
// Usamos include para mantener consistencia con tus otros archivos api
include '../../config/db.php'; 

try {
    // Seleccionamos los campos exactos que requiere el JS para filtrar y mostrar
    $query = "SELECT id, nombre, clave, carrera_id, semestre FROM materias ORDER BY semestre, nombre ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay materias, devolvemos un array vacío en lugar de error
    echo json_encode($materias);

} catch (PDOException $e) {
    // Respuesta en formato JSON para que el fetch del JS no truene
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>