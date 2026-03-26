<?php
// Ocultar errores de PHP para que no arruinen la respuesta AJAX
error_reporting(0); 

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Recibir los datos enviados por AJAX (fetch)
    $alumno_id = $_POST['alumno_id'] ?? 0;
    $grupo_id = $_POST['grupo_id'] ?? 0;
    $numero_unit = $_POST['numero_unit'] ?? 1;
    $nota = $_POST['nota'] ?? 0;

    // Validación básica
    if ($alumno_id == 0 || $grupo_id == 0) {
        echo "Error: Faltan datos críticos (Alumno o Grupo).";
        exit;
    }

    // Asegurarnos de que la nota esté en formato decimal para la BD
    $nota = (float)$nota;

    // 2. Verificar si la Unidad ya existe para este Grupo
    // Usamos el número de unidad (1, 2, 3 o 4) y el ID del grupo
    $query_unidad = "SELECT id FROM unidades WHERE grupo_id = '$grupo_id' AND numero_unit = '$numero_unit' LIMIT 1";
    $res_unidad = mysqli_query($conexion, $query_unidad);
    
    if (mysqli_num_rows($res_unidad) > 0) {
        // La unidad ya existe, sacamos su ID
        $u = mysqli_fetch_assoc($res_unidad);
        $unidad_id = $u['id'];
    } else {
        // La unidad no existe, LA CREAMOS MÁGICAMENTE
        // Asignamos una ponderación de 25.00 por defecto para que las 4 unidades sumen 100
        $insert_unidad = "INSERT INTO unidades (grupo_id, numero_unit, nombre_unidad, ponderacion) 
                          VALUES ('$grupo_id', '$numero_unit', 'Unidad $numero_unit', 25.00)";
        
        if (mysqli_query($conexion, $insert_unidad)) {
            $unidad_id = mysqli_insert_id($conexion);
        } else {
            echo "Error DB al crear Unidad: " . mysqli_error($conexion);
            exit;
        }
    }

    // 3. Insertar o Actualizar la calificación
    // Si ya existe una nota para este alumno en esta unidad, la sobreescribe (ON DUPLICATE KEY UPDATE)
    $sql_calif = "INSERT INTO calificaciones_unidades (alumno_id, unidad_id, nota_final) 
                  VALUES ('$alumno_id', '$unidad_id', '$nota') 
                  ON DUPLICATE KEY UPDATE nota_final = '$nota'";
    
    if (mysqli_query($conexion, $sql_calif)) {
        // Si todo sale bien, imprimimos exactamente la palabra "success"
        // Esto es lo que lee JavaScript para poner el botón en verde
        echo "success";
    } else {
        echo "Error DB al guardar Calificación: " . mysqli_error($conexion);
    }
} else {
    echo "Error: Método no permitido. Usa POST.";
}
?>