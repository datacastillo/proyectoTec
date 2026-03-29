<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Este es el ID que nos manda el botón
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
        // 1. Asumimos que el JS manda el usuario_id (lo más común)
        $res = mysqli_query($conexion, "SELECT usuario_id FROM alumnos WHERE usuario_id = $id_enviado");
        if (mysqli_num_rows($res) > 0) {
            $usuario_id_a_borrar = $id_enviado;
            mysqli_query($conexion, "DELETE FROM alumnos WHERE usuario_id = $id_enviado");
        } else {
            // Fallback: Por si acaso mandó el id directo de la tabla alumnos
            $res2 = mysqli_query($conexion, "SELECT usuario_id FROM alumnos WHERE id = $id_enviado");
            if ($row2 = mysqli_fetch_assoc($res2)) {
                $usuario_id_a_borrar = $row2['usuario_id'];
                mysqli_query($conexion, "DELETE FROM alumnos WHERE id = $id_enviado");
            } else {
                throw new Exception("No se encontró el registro del alumno.");
            }
        }
        
    } else if ($tipo === 'docente') {
        // 1. Asumimos que el JS manda el usuario_id
        $res = mysqli_query($conexion, "SELECT usuario_id FROM docentes WHERE usuario_id = $id_enviado");
        if (mysqli_num_rows($res) > 0) {
            $usuario_id_a_borrar = $id_enviado;
            mysqli_query($conexion, "DELETE FROM docentes WHERE usuario_id = $id_enviado");
        } else {
            // Fallback: Por si acaso mandó el id de la tabla docentes
            $res2 = mysqli_query($conexion, "SELECT usuario_id FROM docentes WHERE id = $id_enviado");
            if ($row2 = mysqli_fetch_assoc($res2)) {
                $usuario_id_a_borrar = $row2['usuario_id'];
                mysqli_query($conexion, "DELETE FROM docentes WHERE id = $id_enviado");
            } else {
                throw new Exception("No se encontró el registro del docente.");
            }
        }
        
    } else if ($tipo === 'materia') {
        // AGREGADO: Soporte para borrar materias
        mysqli_query($conexion, "DELETE FROM materias WHERE id = $id_enviado");
        if (mysqli_affected_rows($conexion) > 0) {
            mysqli_commit($conexion);
            echo json_encode(['success' => true]);
            exit(); // Terminamos aquí porque la materia no tiene cuenta en 'usuarios'
        } else {
            throw new Exception("No se encontró el registro de la materia.");
        }
        
    } else {
        throw new Exception("Tipo de registro no válido.");
    }

    // 3. Si encontramos el usuario_id, borramos la cuenta principal
    if ($usuario_id_a_borrar > 0) {
        mysqli_query($conexion, "DELETE FROM usuarios WHERE id = $usuario_id_a_borrar");
        
        // Confirmamos que se borró el usuario
        if (mysqli_affected_rows($conexion) > 0) {
            mysqli_commit($conexion);
            echo json_encode(['success' => true]);
        } else {
            // Si por alguna razón el perfil existía pero la cuenta de usuario no, igual guardamos el cambio
            mysqli_commit($conexion);
            echo json_encode(['success' => true]); 
        }
    }

} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_query($conexion, "SET FOREIGN_KEY_CHECKS = 1");
?>