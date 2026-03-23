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
    
    // Limpiamos el email de espacios accidentales
    $email = trim($_POST['email']);
    $password = $_POST['password']; 

    // 4. Verificamos la variable de conexión ($conexion) definida en db.php
    if (!isset($conexion)) {
        die("Error: La variable \$conexion no está definida en db.php");
    }

    // 5. Consulta SQL con LEFT JOIN para traer datos de alumno si existen
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
            // --- SOLUCIÓN PARA CHAR(60) ---
            // trim() elimina los espacios en blanco que MySQL añade al final en campos CHAR
            $pass_db = trim($usuario['password']);

            // 6. VALIDACIÓN: Compara texto plano (pass123) O Hash encriptado
            if ($password === $pass_db || password_verify($password, $pass_db)) {
                
                // Guardamos los datos base en la sesión
                $_SESSION['id_usuario'] = $usuario['id'];
                $_SESSION['nombre']     = $usuario['nombre_completo'];
                $_SESSION['rol']        = $usuario['rol'];

                // 7. REDIRECCIÓN SEGÚN ROL Y ESTRUCTURA DE PROYECTO
                if ($usuario['rol'] == 'alumno') {
                    $_SESSION['alumno_id'] = $usuario['alumno_id'];
                    $_SESSION['matricula'] = $usuario['matricula'];
                    
                    // Ruta corregida a la subcarpeta de Materias
                    header("Location: ../Alumno/Materias/Index.php");

                } elseif ($usuario['rol'] == 'admin') {
                    // Ruta actualizada según el módulo de tu compañero (Administrador/admin.html)
                    header("Location: ../Administrador/admin.html");

                } elseif ($usuario['rol'] == 'docente') {
                    // Ruta estándar para docentes
                    header("Location: ../Docente/index.php");

                } else {
                    // Por si existe algún otro rol no definido
                    header("Location: login.html");
                }
                exit();

            } else {
                echo "<script>alert('Contraseña incorrecta.'); window.location.href='login.html';</script>";
            }
        } else {
            echo "<script>alert('El correo institucional no está registrado o el usuario está inactivo.'); window.location.href='login.html';</script>";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Si intentan entrar directo al archivo por URL, mandarlos al login
    header("Location: login.html");
    exit();
}