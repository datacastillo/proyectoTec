<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de administrador']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recibir datos y limpiar para evitar inyección SQL
    $id_enviado = isset($_POST['id']) ? intval($_POST['id']) : 0; // Si no hay ID, es 0 (Nuevo)
    $nombre   = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo   = mysqli_real_escape_string($conexion, $_POST['correo']);
    $password = $_POST['password']; 
    $rol      = mysqli_real_escape_string($conexion, $_POST['rol']);
    $extra    = mysqli_real_escape_string($conexion, $_POST['extra']);

    // Validar campos obligatorios (nombre, correo, extra)
    if (empty($nombre) || empty($correo) || empty($extra)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit();
    }

    if ($id_enviado > 0) {
        // ==========================================
        // MODO EDICIÓN (UPDATE)
        // ==========================================
        $usuario_id_a_editar = 0;

        // 1. Buscamos el usuario_id real
        if ($rol === 'alumno') {
            $res = mysqli_query($conexion, "SELECT usuario_id FROM alumnos WHERE id = $id_enviado");
        } else {
            $res = mysqli_query($conexion, "SELECT usuario_id FROM docentes WHERE id = $id_enviado");
        }

        if ($row = mysqli_fetch_assoc($res)) {
            $usuario_id_a_editar = $row['usuario_id'];
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el registro para editar.']);
            exit();
        }

        // 2. Actualizamos la tabla principal (usuarios)
        $query_user = "UPDATE usuarios SET nombre_completo = '$nombre', correo = '$correo' ";
        
        // Si escribieron una contraseña nueva, la encriptamos y la actualizamos. Si está vacía, no se toca.
        if (!empty($password)) {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $query_user .= ", password = '$pass_hash' ";
        }
        $query_user .= " WHERE id = $usuario_id_a_editar";
        
        mysqli_query($conexion, $query_user);

        // 3. Actualizamos la tabla específica (alumnos o docentes)
        if ($rol === 'alumno') {
            $query_esp = "UPDATE alumnos SET matricula = '$extra' WHERE id = $id_enviado";
        } else {
            $query_esp = "UPDATE docentes SET especialidad = '$extra' WHERE id = $id_enviado";
        }
        
        if (mysqli_query($conexion, $query_esp)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar datos específicos: ' . mysqli_error($conexion)]);
        }

    } else {
        // ==========================================
        // MODO CREACIÓN (INSERT) - (Tu código original)
        // ==========================================
        
        // Para crear un usuario nuevo, la contraseña SÍ es obligatoria
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios.']);
            exit();
        }

        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $query_user = "INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) 
                       VALUES ('$nombre', '$correo', '$pass_hash', '$rol', 1)";

        if (mysqli_query($conexion, $query_user)) {
            $id_usuario = mysqli_insert_id($conexion);

            if ($rol === 'alumno') {
                $query_esp = "INSERT INTO alumnos (usuario_id, carrera_id, matricula, fecha_ingreso) 
                              VALUES ($id_usuario, 1, '$extra', CURDATE())";
            } else {
                $query_esp = "INSERT INTO docentes (usuario_id, especialidad) 
                              VALUES ($id_usuario, '$extra')";
            }

            if (mysqli_query($conexion, $query_esp)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en tabla específica: ' . mysqli_error($conexion)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear credenciales: ' . mysqli_error($conexion)]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>