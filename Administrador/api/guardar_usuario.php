<?php
session_start();
header('Content-Type: application/json');

// Ajusta la ruta a db.php según tu estructura
// Si estás en /Administrador/api/ y db.php está en /config/
require_once '../../config/db.php';

// 1. Validar Sesión y Rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de administrador']);
    exit();
}

// 2. Procesar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Limpiar datos para evitar Inyección SQL
    $nombre   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo   = mysqli_real_escape_string($conexion, $_POST['correo']);
    $password = $_POST['password']; 
    $rol      = mysqli_real_escape_string($conexion, $_POST['rol']); // 'alumno' o 'docente'
    $extra    = mysqli_real_escape_string($conexion, $_POST['extra']); // Matrícula o Especialidad

    // Validar que no vengan vacíos
    if (empty($nombre) || empty($correo) || empty($password) || empty($extra)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit();
    }

    // Encriptar contraseña para que sea compatible con password_verify en el login
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // --- PASO 1: Insertar en la tabla principal 'usuarios' ---
    $query_user = "INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) 
                   VALUES ('$nombre', '$correo', '$pass_hash', '$rol', 1)";

    if (mysqli_query($conexion, $query_user)) {
        // Obtenemos el ID del usuario recién creado
        $id_usuario = mysqli_insert_id($conexion);

        // --- PASO 2: Insertar en la tabla específica según el Rol ---
        if ($rol === 'alumno') {
            // Se asocia a carrera_id 1 por defecto (Sistemas)
            $query_esp = "INSERT INTO alumnos (usuario_id, carrera_id, matricula, fecha_ingreso) 
                          VALUES ($id_usuario, 1, '$extra', CURDATE())";
        } else {
            // Para docentes
            $query_esp = "INSERT INTO docentes (usuario_id, especialidad) 
                          VALUES ($id_usuario, '$extra')";
        }

        if (mysqli_query($conexion, $query_esp)) {
            // ÉXITO TOTAL
            echo json_encode(['success' => true]);
        } else {
            // Error en la segunda tabla (limpiar rastro en usuarios si es necesario, 
            // aunque aquí solo informamos el error)
            echo json_encode([
                'success' => false, 
                'message' => 'Usuario creado, pero hubo error en tabla específica: ' . mysqli_error($conexion)
            ]);
        }
    } else {
        // Error al insertar en la tabla usuarios (ej: correo duplicado)
        echo json_encode([
            'success' => false, 
            'message' => 'Error al crear credenciales: ' . mysqli_error($conexion)
        ]);
    }
} else {
    // Si intentan entrar por URL sin POST
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>