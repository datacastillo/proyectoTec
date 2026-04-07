<?php
// 1. Conexión a la base de datos
require_once '../config/db.php'; 

// 2. Cabecera JSON
header('Content-Type: application/json');

// 3. Recibir y limpiar datos
$folio = isset($_POST['folio']) ? trim($_POST['folio']) : '';
$curp  = isset($_POST['curp']) ? strtoupper(trim($_POST['curp'])) : '';

if (empty($folio) || empty($curp)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, ingresa Folio y CURP.']);
    exit;
}

try {
    // 4. Buscar SOLO por Folio primero para ver si existe
    $stmt = $pdo->prepare("SELECT s.*, c.nombre AS carrera_nombre 
                           FROM solicitudes_fichas s 
                           LEFT JOIN carreras c ON s.carrera_id = c.id 
                           WHERE s.folio = ?");
    $stmt->execute([$folio]);
    $ficha = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validación 1: ¿Existe el folio?
    if (!$ficha) {
        echo json_encode(['success' => false, 'message' => "El Folio '$folio' no existe en la base de datos. Verifica que lo hayas escrito bien."]);
        exit;
    }

    // Validación 2: ¿El CURP coincide?
    if (strtoupper(trim($ficha['curp'])) !== $curp) {
        echo json_encode(['success' => false, 'message' => "El CURP ingresado no coincide con el registrado para el folio '$folio'."]);
        exit;
    }

    // Validación 3: ¿El estatus es correcto?
    if (!in_array($ficha['estatus'], ['aprobada', 'inscrito'])) {
        echo json_encode(['success' => false, 'message' => "El estatus actual del alumno es: '" . strtoupper($ficha['estatus']) . "'. Necesita ser APROBADA o INSCRITO."]);
        exit;
    }

    // 5. Si pasa todas las pruebas, traemos las materias
    $id_carrera = $ficha['carrera_id'];
    
    $stmt_m = $pdo->prepare("SELECT clave, nombre FROM materias WHERE carrera_id = ?");
    $stmt_m->execute([$id_carrera]);
    $materias = $stmt_m->fetchAll(PDO::FETCH_ASSOC);

    // 6. Respuesta de éxito (REGRESAMOS A TU COLUMNA 'apellido')
    echo json_encode([
        'success' => true,
        'nombre' => $ficha['nombre'] . " " . $ficha['apellido'],
        'carrera' => $ficha['carrera_nombre'],
        'materias' => $materias
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de Base de Datos: ' . $e->getMessage()]);
}
?>