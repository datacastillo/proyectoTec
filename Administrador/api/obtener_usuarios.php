<?php
header('Content-Type: application/json');
include '../../config/db.php'; 

$tipo = $_GET['tipo'] ?? '';

try {
    if ($tipo === 'alumno') {
        // Consulta para ALUMNOS
        $query = "SELECT u.id, u.nombre_completo, u.correo, a.matricula AS extra 
                  FROM usuarios u 
                  INNER JOIN alumnos a ON u.id = a.usuario_id 
                  WHERE u.rol = 'alumno' AND u.activo = 1";
                  
    } else if ($tipo === 'docente') {
        // Consulta para DOCENTES: Agregamos d.id AS docente_id para evitar el error de llave foránea
        $query = "SELECT u.id, u.nombre_completo, u.correo, d.especialidad AS extra, d.id AS docente_id 
                  FROM usuarios u 
                  INNER JOIN docentes d ON u.id = d.usuario_id 
                  WHERE u.rol = 'docente' AND u.activo = 1";
                  
    } else {
        echo json_encode(["error" => "Tipo de usuario no válido"]);
        exit;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($resultados);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error DB: " . $e->getMessage()]);
}
?>