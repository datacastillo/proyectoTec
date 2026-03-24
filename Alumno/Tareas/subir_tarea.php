<?php
session_start();
require_once '../../config/db.php';

//   Solo alumnos pueden subir tareas
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

// Verificar que se haya enviado un archivo y el ID de la tarea
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_pdf'])) {
    
    $tarea_id = $_POST['tarea_id'];
    $id_usuario = $_SESSION['id_usuario']; 
    $matricula = $_SESSION['matricula'];

    $query_alu = "SELECT id FROM alumnos WHERE usuario_id = '$id_usuario'";
    $res_alu = mysqli_query($conexion, $query_alu);
    $datos_alu = mysqli_fetch_assoc($res_alu);
    
    if (!$datos_alu) {
        die("Error: No se encontró el registro del alumno.");
    }
    
    $alumno_id = $datos_alu['id'];

    $directorio = "uploads/";
    $nombre_original = $_FILES['archivo_pdf']['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    
    // Nombre único para evitar que archivos se sobreescriban
    $nombre_nuevo = "T" . $tarea_id . "_" . $matricula . "_" . time() . "." . $extension;
    $ruta_final = $directorio . $nombre_nuevo;

    //  Validar que sea PDF
    if ($extension != "pdf") {
        echo "<script>alert('Error: Solo se permiten archivos PDF.'); window.history.back();</script>";
        exit();
    }

    //  Mover el archivo al servidor y registrar en BD
    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $ruta_final)) {
        
        $sql = "INSERT INTO entregas (tarea_id, alumno_id, archivo_alumno, estatus) 
                VALUES (?, ?, ?, 'entregado')";

        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $tarea_id, $alumno_id, $nombre_nuevo);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('¡Tarea subida con éxito!'); window.location.href='tareas.php';</script>";
            } else {
                echo "Error al registrar en BD: " . mysqli_error($conexion);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        echo "Error: No se pudo mover el archivo. Verifica que la carpeta 'uploads' exista en Alumno/Tareas/.";
    }
} else {
    // Si alguien intenta entrar directo al archivo sin subir nada, lo mandamos de regreso
    header("Location: tareas.php");
}
?>