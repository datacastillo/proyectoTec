<?php
session_start();
// Salimos de 'auth' para buscar la conexión en la raíz
$path_db = __DIR__ . '/../config/db.php'; 

if (file_exists($path_db)) {
    require_once $path_db;
} else {
    die("Error crítico: No se encontró la conexión en: " . $path_db);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password_ingresada = $_POST['password']; 

    $query = "SELECT u.id, u.nombre_completo, u.password, u.rol, a.matricula 
              FROM usuarios u 
              LEFT JOIN alumnos a ON u.id = a.usuario_id 
              WHERE u.correo = '$email' LIMIT 1";
              
    $resultado = mysqli_query($conexion, $query);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        $login_exitoso = false;

        if (password_verify($password_ingresada, $usuario['password'])) {
            $login_exitoso = true; 
        } elseif ($password_ingresada === $usuario['password']) {
            $login_exitoso = true; 
        } elseif ($usuario['rol'] === 'alumno' && $password_ingresada === $usuario['matricula']) {
            $login_exitoso = true; 
        }

        if ($login_exitoso) {
            session_regenerate_id(true);
            // --- NOMBRES ESTÁNDAR PARA TODO EL PROYECTO ---
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'docente') {
                header("Location: ../Docente/docente.php");
            } elseif ($usuario['rol'] === 'alumno') {
                header("Location: ../Alumno/Materias/Index.php");
            } else {
                header("Location: ../index.html");
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='../login.html';</script>";
        }
    } else {
        echo "<script>alert('Correo no registrado.'); window.location.href='../login.html';</script>";
    }
}