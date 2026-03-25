<?php
session_start();
$path_db = __DIR__ . '/../config/db.php';

if (file_exists($path_db)) {
    require_once $path_db;
} else {
    die("Error crítico: No se encontró la conexión.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    
    $email = trim($_POST['email']);
    $password_ingresada = trim($_POST['password']); 

    // Traemos todos los datos necesarios: de usuarios y de alumnos (si existe)
    $sql = "SELECT u.id, u.nombre_completo, u.rol, u.password as pass_hash, a.matricula, a.id as alumno_id 
            FROM usuarios u 
            LEFT JOIN alumnos a ON u.id = a.usuario_id 
            WHERE u.correo = ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($result);

        if ($usuario) {
            $acceso_concedido = false;

            // LÓGICA DE VALIDACIÓN POR ROL
            if ($usuario['rol'] == 'alumno') {
                // Para alumnos: La "contraseña" es su Matrícula
                if ($password_ingresada === $usuario['matricula']) {
                    $acceso_concedido = true;
                }
            } else {
                // Para Admin/Docentes: Usamos la contraseña (hash) de la tabla usuarios
                if (password_verify($password_ingresada, trim($usuario['pass_hash']))) {
                    $acceso_concedido = true;
                }
            }

            if ($acceso_concedido) {
                // Seteamos las variables de sesión que tus otros archivos necesitan
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['nombre']     = $usuario['nombre_completo'];
                $_SESSION['rol']        = $usuario['rol'];
                $_SESSION['matricula']  = $usuario['matricula'] ?? 'N/A';
                $_SESSION['alumno_id']  = $usuario['alumno_id'] ?? null;

                // Redirecciones
                if ($usuario['rol'] == 'alumno') {
                    header("Location: ../Alumno/Materias/index.php");
                } elseif ($usuario['rol'] == 'admin') {
                    header("Location: ../Administrador/admin.html");
                } else {
                    header("Location: ../Docente/index.php");
                }
                exit();
            } else {
                $msg = ($usuario['rol'] == 'alumno') ? 'Matrícula incorrecta.' : 'Contraseña incorrecta.';
                echo "<script>alert('$msg'); window.location.href='login.html';</script>";
            }
        } else {
            echo "<script>alert('El correo no está registrado.'); window.location.href='login.html';</script>";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    header("Location: login.html");
    exit();
}