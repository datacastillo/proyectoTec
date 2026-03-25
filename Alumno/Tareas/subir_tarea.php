<?php
session_start();
require_once '../../config/db.php';

// Solo alumnos pueden subir tareas
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_pdf'])) {
    
    $tarea_id = $_POST['tarea_id'];
    $id_usuario = $_SESSION['id_usuario']; 
    $matricula = $_SESSION['matricula'];

    // Buscamos el ID del alumno
    $query_alu = "SELECT id FROM alumnos WHERE usuario_id = ?";
    $stmt_alu = mysqli_prepare($conexion, $query_alu);
    mysqli_stmt_bind_param($stmt_alu, "i", $id_usuario);
    mysqli_stmt_execute($stmt_alu);
    $res_alu = mysqli_stmt_get_result($stmt_alu);
    $datos_alu = mysqli_fetch_assoc($res_alu);
    
    if (!$datos_alu) {
        die("Error: No se encontró el registro del alumno.");
    }
    
    $alumno_id = $datos_alu['id'];

    // Directorio de subida
    $directorio = __DIR__ . "/uploads/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_original = $_FILES['archivo_pdf']['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    
    // Nombre único para el archivo
    $nombre_nuevo = "T" . $tarea_id . "_" . $matricula . "_" . time() . "." . $extension;
    $ruta_final = $directorio . $nombre_nuevo;

    // Validar PDF
    if ($extension != "pdf") {
        echo "<script>alert('Error: Solo se permiten archivos PDF.'); window.history.back();</script>";
        exit();
    }

    // Mover archivo y registrar
    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $ruta_final)) {
        
        // ELIMINÉ 'fecha_entrega' para que coincida con tu tabla
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
        echo "Error al guardar el archivo. Revisa permisos en la carpeta uploads.";
    }
} else {
    header("Location: tareas.php");
}
?>