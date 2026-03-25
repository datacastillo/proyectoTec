<?php
ob_start(); 
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../../config/db.php';

$al_id = isset($_POST['alumno_id']) ? intval($_POST['alumno_id']) : 0;
$n_uni = isset($_POST['unidad']) ? intval($_POST['unidad']) : 0;
$nota  = isset($_POST['nota']) ? mysqli_real_escape_string($conexion, $_POST['nota']) : '';
$gr_id = isset($_POST['grupo_id']) ? intval($_POST['grupo_id']) : 0;

$response = ['success' => false, 'error' => ''];

if ($al_id > 0 && $n_uni > 0 && $gr_id > 0) {
    $q_u = mysqli_query($conexion, "SELECT id FROM unidades WHERE numero_unit = '$n_uni' AND grupo_id = '$gr_id' LIMIT 1");
    $u_data = mysqli_fetch_assoc($q_u);
    $unidad_id = $u_data['id'] ?? 0;

    if ($unidad_id > 0) {
        $check = mysqli_query($conexion, "SELECT id FROM calificaciones_unidades WHERE alumno_id = '$al_id' AND unidad_id = '$unidad_id'");
        
        if (mysqli_num_rows($check) > 0) {
            // Actualizar
            $sql = "UPDATE calificaciones_unidades SET nota_final = '$nota' WHERE alumno_id = '$al_id' AND unidad_id = '$unidad_id'";
        } else {
            // Insertar nuevo
            $sql = "INSERT INTO calificaciones_unidades (alumno_id, unidad_id, nota_final) VALUES ('$al_id', '$unidad_id', '$nota')";
        }

        if (mysqli_query($conexion, $sql)) {
            $response['success'] = true;
        } else {
            $response['error'] = "Error DB: " . mysqli_error($conexion);
        }
    } else {
        $response['error'] = "No se encontró la configuración de la Unidad $n_uni para el grupo $gr_id";
    }
} else {
    $response['error'] = "Datos incompletos o inválidos enviados al servidor.";
}

ob_end_clean();
echo json_encode($response);
exit;