<?php
session_start();
require_once '../../config/db.php';

$grupo_id = $_GET['grupo_id'] ?? 0;

$query = "SELECT a.id, a.matricula, u.nombre_completo 
          FROM inscripciones i
          JOIN alumnos a ON i.alumno_id = a.id
          JOIN usuarios u ON a.usuario_id = u.id
          WHERE i.grupo_id = '$grupo_id'";
$res = mysqli_query($conexion, $query);

if(mysqli_num_rows($res) > 0) {
    echo '<table style="width: 100%; color: white; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #3e92cc;">
                    <th style="padding: 10px; text-align: left;">Matrícula</th>
                    <th style="padding: 10px; text-align: left;">Alumno</th>
                    <th style="padding: 10px; text-align: center;">Nota (0-100)</th>
                    <th style="padding: 10px; text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>';
    while($row = mysqli_fetch_assoc($res)) {
        echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.1);'>
                <td style='padding: 10px;'>{$row['matricula']}</td>
                <td style='padding: 10px;'>".strtoupper($row['nombre_completo'])."</td>
                <td style='padding: 10px; text-align: center;'>
                    <input type='number' id='nota_{$row['id']}' style='width: 60px; padding: 5px; background: #0d1b2a; color: white; border: 1px solid #3e92cc; text-align: center;' min='0' max='100'>
                </td>
                <td style='padding: 10px; text-align: center;'>
                    <button onclick='guardarCalificacion({$row['id']}, $grupo_id)' style='background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>Guardar</button>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo '<p style="color: #adb5bd; text-align: center;">No hay alumnos inscritos en este grupo.</p>';
}