<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$correo   = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

// Validar que no lleguen vacíos
if (empty($correo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
    exit;
}

// Buscar usuario activo por correo
$sql  = "SELECT id, nombre_completo, password, rol FROM usuarios WHERE correo = ? AND activo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$correo]);
$usuario = $stmt->fetch();

// Verificar que exista y que la contraseña sea correcta
if (!$usuario || !password_verify($password, $usuario['password'])) {
    echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
    exit;
}

// Guardar datos en sesión
$_SESSION['usuario_id']     = $usuario['id'];
$_SESSION['nombre']         = $usuario['nombre_completo'];
$_SESSION['rol']            = $usuario['rol'];

// Actualizar último login
$upd = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
$upd->execute([$usuario['id']]);

// Redirigir según rol
$rutas = [
    'alumno'  => '../Alumno/Materias/Index.html',
    'docente' => '../Docente/MateriasDocente/docenteM.html',
    'admin'   => '../index.html', // ajustar cuando exista el panel admin
];

$redirect = $rutas[$usuario['rol']] ?? '../index.html';

echo json_encode([
    'success'  => true,
    'rol'      => $usuario['rol'],
    'nombre'   => $usuario['nombre_completo'],
    'redirect' => $redirect
]);
?>
