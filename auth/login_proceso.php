<?php
// 1. Iniciamos la sesión
session_start();

// 2. Cargamos la conexión (Ruta: sube un nivel de 'auth' y entra a 'config')
$path_db = __DIR__ . '/../config/db.php';

if (file_exists($path_db)) {
    require_once $path_db;
} else {
    die("Error crítico: No se encontró el archivo de conexión en: " . $path_db);
}

// 3. Verificamos que los datos lleguen del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    
    // Usamos 'email' porque así se llama el campo en tu login.html
    $email = trim($_POST['email']);
    $password = $_POST['password']; 

    // 4. Verificamos la variable de conexión de tu db.php ($conexion)
    if (!isset($conexion)) {
        die("Error: La variable \$conexion no está definida en db.php");
    }

    // 5. Consulta SQL con LEFT JOIN para traer los datos del alumno
    $sql = "SELECT u.*, a.id as alumno_id, a.matricula 
            FROM usuarios u 
            LEFT JOIN alumnos a ON u.id = a.usuario_id 
            WHERE u.correo = ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($result);

        if ($usuario) {
            $pass_db = trim($usuario['password']);

            // 6. VALIDACIÓN: Compara texto plano (como el 'pass123' de Nahomi) o Hash
            if ($password === $pass_db || password_verify($password, $pass_db)) {
                
                // Guardamos los datos en la sesión
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre_completo'];
                $_SESSION['rol'] = $usuario['rol'];

                // 7. REDIRECCIÓN SEGÚN TU ESTRUCTURA DE CARPETAS
                if ($usuario['rol'] == 'alumno') {
                    $_SESSION['alumno_id'] = $usuario['alumno_id'];
                    $_SESSION['matricula'] = $usuario['matricula'];
                    
                    // SEGÚN TU IMAGEN: Redirigimos a Materias/Index.php porque no hay index en la raíz de Alumno
                    header("Location: ../Alumno/Materias/Index.php");
                } elseif ($usuario['rol'] == 'docente') {
                    // Ajusta esta ruta si tu docente también tiene subcarpetas
                    header("Location: ../Docente/index.php");
                } else {
                    header("Location: ../Admin/index.php");
                }
                exit();

            } else {
                echo "<script>alert('Contraseña incorrecta.'); window.location.href='login.html';</script>";
            }
        } else {
            echo "<script>alert('El correo institucional no está registrado.'); window.location.href='login.html';</script>";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Si intentan entrar directo al archivo, mandarlos al login
    header("Location: login.html");
    exit();
}