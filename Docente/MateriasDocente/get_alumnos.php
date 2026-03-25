<?php
require_once '../../config/db.php';
$grupo_id = isset($_GET['grupo_id']) ? intval($_GET['grupo_id']) : 0;

if($grupo_id == 0) exit;

$q_u = mysqli_query($conexion, "SELECT id, numero_unit FROM unidades WHERE grupo_id = '$grupo_id' ORDER BY numero_unit ASC");
$mapa_u = [];
while($u = mysqli_fetch_assoc($q_u)) { $mapa_u[$u['numero_unit']] = $u['id']; }

$query = "SELECT u.id, u.nombre_completo FROM usuarios u 
          JOIN inscripciones i ON u.id = i.alumno_id 
          WHERE i.grupo_id = '$grupo_id' ORDER BY u.nombre_completo ASC";
$res = mysqli_query($conexion, $query);

while($row = mysqli_fetch_assoc($res)) {
    $al_id = $row['id'];
    $q_c = mysqli_query($conexion, "SELECT unidad_id, nota_final FROM calificaciones_unidades WHERE alumno_id = '$al_id'");
    $cals = [];
    while($c = mysqli_fetch_assoc($q_c)) { $cals[$c['unidad_id']] = $c['nota_final']; }

    echo "<tr><td style='text-align:left; padding-left:15px;'>".htmlspecialchars($row['nombre_completo'])."</td>";
    
    $suma = 0; $cont = 0;
    for($i=1; $i<=4; $i++) {
        $u_id = $mapa_u[$i] ?? 0;
        $nota = isset($cals[$u_id]) ? $cals[$u_id] : '-';
        $color = ($nota !== '-' && $nota < 70) ? '#ff4444' : ($nota === '-' ? '#888' : '#00ff00');
        
        echo "<td onclick=\"abrirModal($al_id, $i, '$nota', $grupo_id)\" style='cursor:pointer; font-weight:bold; color:$color;'>$nota</td>";
        
        if(is_numeric($nota)) { $suma += $nota; $cont++; }
    }
    $prom = ($cont > 0) ? round($suma / $cont, 1) : '-';
    echo "<td><strong>$prom</strong></td></tr>";
}