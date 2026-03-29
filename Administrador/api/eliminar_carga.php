<?php
session_start();
require_once '../../config/db.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'No se especificó qué grupo eliminar.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM grupos WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Grupo eliminado correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
?><?php
header('Content-Type: application/json');
include '../../config/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM grupos WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Asignación eliminada"]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontró el registro"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ID no proporcionado"]);
}
?>