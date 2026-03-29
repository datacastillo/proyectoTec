<?php
header('Content-Type: application/json');
include '../../config/db.php';

try {
    // 1. Usamos nombre_completo de la tabla usuarios
    // 2. Traemos el id de la tabla grupos para poder eliminar después
    $sql = "SELECT 
                g.id, 
                u.nombre_completo AS docente_nombre, 
                m.nombre AS materia_nombre, 
                g.nombre_grupo, 
                g.semestre, 
                g.ciclo_escolar 
            FROM grupos g
            INNER JOIN usuarios u ON g.docente_id = u.id
            INNER JOIN materias m ON g.materia_id = m.id
            ORDER BY g.ciclo_escolar DESC, g.semestre ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviamos los datos (o un array vacío si no hay nada)
    echo json_encode($data ? $data : []);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
}
?>