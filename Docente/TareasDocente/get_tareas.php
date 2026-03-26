<?php
require_once '../../config/db.php';

// Validamos que llegue el ID de la materia
$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;

if ($materia_id > 0) {
    /**
     * RELACIÓN CONFIRMADA:
     * t.unidad_id -> u.id
     * u.grupo_id  -> g.id
     */
    $query = "SELECT t.*, u.nombre_unidad, u.numero_unit 
              FROM tareas t 
              JOIN unidades u ON t.unidad_id = u.id 
              JOIN grupos g ON u.grupo_id = g.id 
              WHERE g.materia_id = '$materia_id' 
              ORDER BY t.fecha_entrega_limite DESC";

    $res = mysqli_query($conexion, $query);

    if (!$res) {
        die("<p style='color:red;'>Error SQL: " . mysqli_error($conexion) . "</p>");
    }

    if (mysqli_num_rows($res) > 0) {
        while ($t = mysqli_fetch_assoc($res)) {
            // Escapamos el título para que no rompa el JavaScript del onclick
            $titulo_js = addslashes($t['titulo']);
            
            // Construimos la etiqueta de la unidad
            $tag_unidad = "U" . $t['numero_unit'] . " - " . htmlspecialchars($t['nombre_unidad']);
            
            // Aseguramos que los puntos no salgan en 0 si la columna es puntos_maximos
            $puntos = ($t['puntos_maximos'] > 0) ? $t['puntos_maximos'] : "N/A";

            echo "
            <div class='tarea-card' style='margin-bottom: 15px; border-left: 5px solid #00ff00;'>
                <div class='tarea-info'>
                    <h4 style='margin: 0; color: #00ff00;'>" . htmlspecialchars($t['titulo']) . "</h4>
                    <p style='color: #888; font-size: 0.85rem; margin: 5px 0;'>{$tag_unidad}</p>
                    <p style='margin: 5px 0;'>
                        <strong>📅 Límite:</strong> {$t['fecha_entrega_limite']} | 
                        <strong>🎯 Puntos:</strong> {$puntos}
                    </p>
                    <p style='color:#ccc; font-size: 0.9rem; margin-top:8px; line-height: 1.4;'>" . 
                        nl2br(htmlspecialchars($t['descripcion'])) . "
                    </p>
                </div>
                <div class='tarea-actions'>
                    <button onclick='verEntregas({$t['id']}, \"$titulo_js\")' 
                            style='background:#00ff00; border:none; padding:10px 20px; cursor:pointer; 
                                   border-radius:5px; font-weight:bold; color: black;'>
                        VER ENTREGAS
                    </button>
                </div>
            </div>";
        }
    } else {
        echo "<div style='text-align:center; padding:40px; color:#666; border: 1px dashed #333; border-radius: 10px;'>
                <p>No hay tareas publicadas para esta materia.</p>
              </div>";
    }
} else {
    echo "<p style='color:red; text-align:center;'>ID de materia no válido.</p>";
}