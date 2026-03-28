<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Este es el ID de la tabla alumnos o docentes que nos manda el botón
$id_enviado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

if ($id_enviado <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit();
}

mysqli_query($conexion, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_begin_transaction($conexion);

try {
    $usuario_id_a_borrar = 0;

    if ($tipo === 'alumno') {
        // 1. Buscamos el usuario_id asociado a este alumno
        $res = mysqli_query($conexion, "SELECT usuario_id FROM alumnos WHERE id = $id_enviado");
        if ($row = mysqli_fetch_assoc($res)) {
            $usuario_id_a_borrar = $row['usuario_id'];
            
            // 2. Borramos al alumno usando su propio ID
            mysqli_query($conexion, "DELETE FROM alumnos WHERE id = $id_enviado");
        } else {
            throw new Exception("No se encontró el registro del alumno.");
        }
    } else if ($tipo === 'docente') {
        // 1. Buscamos el usuario_id asociado a este docente
        $res = mysqli_query($conexion, "SELECT usuario_id FROM docentes WHERE id = $id_enviado");
        if ($row = mysqli_fetch_assoc($res)) {
            $usuario_id_a_borrar = $row['usuario_id'];
            
            // 2. Borramos al docente usando su propio ID
            mysqli_query($conexion, "DELETE FROM docentes WHERE id = $id_enviado");
        } else {
            throw new Exception("No se encontró el registro del docente.");
        }
    }

    // 3. Si encontramos el usuario_id, borramos la cuenta principal
    if ($usuario_id_a_borrar > 0) {
        mysqli_query($conexion, "DELETE FROM usuarios WHERE id = $usuario_id_a_borrar");
        
        // Confirmamos que se borró el usuario
        if (mysqli_affected_rows($conexion) > 0) {
            mysqli_commit($conexion);
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Se borró el perfil, pero no se encontró la cuenta de usuario principal.");
        }
    } else {
        throw new Exception("No se pudo identificar la cuenta vinculada.");
    }

} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_query($conexion, "SET FOREIGN_KEY_CHECKS = 1");
?>