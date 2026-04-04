<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

// 1. Verificamos que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida o expirada']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. Recibimos los datos del FormData
$telefono = $_POST['telefono'] ?? '';
// Pongo default.png como respaldo por si acaso
$foto_perfil = $_POST['foto_perfil'] ?? 'default.png'; 

// 3. Ejecutamos la actualización usando MySQLi ($conexion)
$sql = "UPDATE usuarios SET telefono = ?, foto_perfil = ? WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);

if ($stmt) {
    // "ssi" significa: String (telefono), String (foto_perfil), Integer (id_usuario)
    mysqli_stmt_bind_param($stmt, "ssi", $telefono, $foto_perfil, $id_usuario);
    $resultado = mysqli_stmt_execute($stmt);

    if ($resultado) {
        // Actualizamos la variable de sesión para que el cambio sea global
        $_SESSION['foto_perfil'] = $foto_perfil; 
        
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado con éxito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar: ' . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    // Si la columna no existe o hay error de sintaxis SQL, lo devolverá en JSON limpio
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . mysqli_error($conexion)]);
}
?>