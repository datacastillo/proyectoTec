<?php
// Incluir este archivo al inicio de cualquier página protegida:
//   require_once '../../auth/session_check.php';
//
// Ajusta la ruta relativa según la profundidad de la página.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) . 'auth/login.html');
    exit;
}
?>
