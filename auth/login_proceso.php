<?php
session_start();
require_once '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];

    // Consulta con JOIN para la matrícula (tabla alumnos)
    $query = "SELECT u.*, a.matricula 
              FROM usuarios u 
              LEFT JOIN alumnos a ON u.id = a.usuario_id 
              WHERE u.correo = '$correo'";
    
    $resultado = mysqli_query($conexion, $query);

    if (mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);

        if ($password == $usuario['password']) {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['matricula'] = ($usuario['rol'] == 'alumno') ? $usuario['matricula'] : 'N/A';

            switch ($usuario['rol']) {
                case 'alumno':
                    header("Location: ../Alumno/Materias/index.php");
                    break;
                case 'docente':
                    header("Location: ../Docente/index.php"); 
                    break;
                case 'admin':
                    header("Location: ../Admin/index.php");
                    break;
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Correo no registrado'); window.location.href='login.html';</script>";
    }
}
?>