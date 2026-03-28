<?php
session_start();

// Ruta corregida: Sube dos niveles para salir de 'api' y 'Administrador' y llegar a 'config'
require_once '../../config/db.php';

// Seguridad: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$tipo = $_GET['tipo'] ?? 'alumno';
$data = [];

try {
    if ($tipo === 'alumno') {
        // En tu tabla usuarios se llama 'nombre_completo'
        $query = "SELECT a.id, u.nombre_completo as nombre, a.matricula as extra 
                  FROM alumnos a 
                  INNER JOIN usuarios u ON a.usuario_id = u.id 
                  WHERE u.rol = 'alumno'";
    } else {
        // Para docentes (ajusta 'especialidad' si el campo se llama distinto en tu tabla docentes)
        $query = "SELECT d.id, u.nombre_completo as nombre, d.especialidad as extra 
                  FROM docentes d 
                  INNER JOIN usuarios u ON d.usuario_id = u.id 
                  WHERE u.rol = 'docente'";
    }

    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $data[] = $fila;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>