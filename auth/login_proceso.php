<?php
// Iniciamos la sesión para poder guardar los datos del usuario logueado
session_start();

// Configuración de rutas y conexión
$path_db = __DIR__ . '/../config/db.php';

if (file_exists($path_db)) {
    require_once $path_db;
} else {-
    die("Error crítico de sistema: No se pudo establecer conexión con la base de datos.");
}

// Verificamos que los datos lleguen por el método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Limpieza de datos (Asegúrate de que en tu HTML el input se llame name="email")
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password']; 

    // 2. Consulta a la base de datos (Usamos 'nombre_completo' según tus tablas)
    $query = "SELECT id, nombre_completo, password, rol FROM usuarios WHERE correo = '$email' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);

        // 3. Verificación de Contraseña
        $password_correcta = false;
        
        // Verifica si es un Hash de PHP o texto plano (para tus pruebas actuales)
        if (password_verify($password, $usuario['password'])) {
            $password_correcta = true;
        } elseif ($password === $usuario['password']) {
            $password_correcta = true;
        }

        if ($password_correcta) {
            // 4. Seguridad: Regenerar ID de sesión
            session_regenerate_id(true);

            // 5. SETEO DE VARIABLES DE SESIÓN (Vital para docenteM.php)
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['ultimo_acceso'] = date("Y-m-d H:i:s");

            // 6. Redireccionamiento basado en ROL
            // IMPORTANTE: Verifica que las carpetas se llamen exactamente así (Mayúsculas/Minúsculas)
            switch ($usuario['rol']) {
                case 'docente':
                    header("Location: ../Docente/docente.php");
                    break;
                case 'alumno':
                    // Ajustado a la ruta común de alumnos
                    header("Location: ../Alumno/alumno.php"); 
                    break;
                case 'admin':
                    header("Location: ../Admin/dashboard.php");
                    break;
                default:
                    header("Location: ../index.html");
                    break;
            }
            exit();

        } else {
            echo "<script>
                    alert('La contraseña ingresada es incorrecta.');
                    window.location.href='login.html';
                  </script>";
        }
    } else {
        echo "<script>
                alert('El correo institucional no se encuentra registrado.');
                window.location.href='login.html';
              </script>";
    }
} else {
    header("Location: login.html");
    exit();
}
?>