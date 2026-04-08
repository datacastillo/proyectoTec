<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Alumno';

$query_alu = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_alu = mysqli_query($conexion, $query_alu);
$reg_alu = mysqli_fetch_assoc($res_alu);

$alumno_id = $reg_alu['id'] ?? 0;
$matricula = $reg_alu['matricula'] ?? 'S/N';

// Hacemos el conteo real de unidades por materia y traemos todas las calificaciones
// OJO: Si tu columna en la BD no se llama "nota_final", cámbiala en el SELECT (ej. cu.calificacion AS nota_final)
$query_kardex = "
    SELECT 
        m.id AS materia_id, 
        m.nombre AS materia_nombre, 
        m.clave,
        u.numero_unit,
        cu.nota_final, 
        (SELECT COUNT(id) FROM unidades WHERE grupo_id = g.id) as total_unidades
    FROM materias m
    INNER JOIN grupos g ON m.id = g.materia_id
    INNER JOIN inscripciones i ON g.id = i.grupo_id
    LEFT JOIN unidades u ON g.id = u.grupo_id 
    LEFT JOIN calificaciones_unidades cu ON u.id = cu.unidad_id AND cu.alumno_id = '$alumno_id'
    WHERE i.alumno_id = '$alumno_id'
    ORDER BY m.nombre, u.numero_unit ASC";
$res_kardex = mysqli_query($conexion, $query_kardex);

$materias_notas = [];
while ($row = mysqli_fetch_assoc($res_kardex)) {
    $m_id = $row['materia_id'];
    
    if (!isset($materias_notas[$m_id])) {
        $materias_notas[$m_id] = [
            'nombre' => $row['materia_nombre'],
            'clave' => $row['clave'],
            // Expandimos a 6 espacios vacíos por defecto
            'notas' => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-', 6 => '-'],
            'total_unidades' => (int)$row['total_unidades']
        ];
    }
    
    // EXTRACCIÓN INTELIGENTE: Si en la BD dice "U1" o "Unidad 1", esto extrae solo el número "1"
    $numero_limpio = (int) preg_replace('/[^0-9]/', '', $row['numero_unit']);

    // Si la unidad es 1 a 6 y tiene calificación válida, la guardamos
    if ($row['nota_final'] !== null && $numero_limpio >= 1 && $numero_limpio <= 6) {
        $materias_notas[$m_id]['notas'][$numero_limpio] = $row['nota_final'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex Académico | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Paleta de colores Azul (Estilo Docente) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* Barra Lateral */
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        /* Contenido Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        /* Estilos del Kardex (Área de Descarga) */
        #areaDescarga { background: #142d3e; padding: 40px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .logo-kardex { max-width: 120px; margin-bottom: 20px; }
        .kardex-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #0d1b2a; }
        .kardex-table th { background: #1b3a57; color: #3e92cc; padding: 15px; border: 1px solid rgba(255,255,255,0.05); text-transform: uppercase; font-size: 13px; }
        .kardex-table td { padding: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center; color: #e0e1dd; font-size: 13px; }
        .text-left { text-align: left !important; }
        .promedio-final { font-weight: bold; font-size: 14px; }
        
        /* Botón PDF */
        .btn-pdf { background: #3e92cc; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 30px; transition: 0.3s; font-size: 14px; letter-spacing: 1px; }
        .btn-pdf:hover { background: #2c7bb6; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(62, 146, 204, 0.4); }
        .btn-pdf:disabled { background: #555; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>

<div class="wrapper">
    
    <?php include '../sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
        </header>

        <section style="padding: 30px; display: flex; flex-direction: column; align-items: center;">
            
            <div id="areaDescarga" style="width: 100%; max-width: 950px; text-align: center;">
                <img src="../img/logoTec.png" class="logo-kardex" alt="Logo Tec">
                <h2 style="color: #fff; margin-bottom: 5px; font-size: 1.8rem;">KÁRDEX ACADÉMICO OFICIAL</h2>
                <p style="color: #adb5bd; margin-bottom: 25px;">Alumno: <strong style="color: #fff;"><?php echo strtoupper($nombreAlumno); ?></strong> | Matrícula: <strong style="color: #fff;"><?php echo $matricula; ?></strong></p>
                
                <table class="kardex-table">
                    <thead>
                        <tr>
                            <th class="text-left">CLAVE - MATERIA</th>
                            <th>U1</th>
                            <th>U2</th>
                            <th>U3</th>
                            <th>U4</th>
                            <th>U5</th>
                            <th>U6</th>
                            <th>PROM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($materias_notas) > 0): ?>
                            <?php foreach ($materias_notas as $m): ?>
                                <tr>
                                    <td class="text-left"><strong style="color: #3e92cc;"><?php echo $m['clave']; ?></strong> - <?php echo strtoupper($m['nombre']); ?></td>
                                    <?php 
                                    $suma = 0; $calificadas = 0;
                                    $total_reales = $m['total_unidades'];

                                    // Iteramos hasta 6 para dibujar todas las columnas
                                    for ($i=1; $i <= 6; $i++): 
                                        $n = $m['notas'][$i];
                                        if (is_numeric($n)) { $suma += $n; $calificadas++; }
                                    ?>
                                        <td><?php echo is_numeric($n) ? number_format($n, 0) : '-'; ?></td>
                                    <?php endfor; ?>
                                    
                                    <?php 
                                        // Mismo sistema inteligente que en la boleta
                                        $tiene_todo = ($calificadas == $total_reales && $total_reales > 0);
                                        $promedio = '-';
                                        $color_promedio = '#adb5bd'; // Gris por defecto
                                        $bg_promedio = 'transparent';

                                        if ($tiene_todo) {
                                            $promedio_num = round($suma / $total_reales, 0);
                                            $promedio = $promedio_num;
                                            
                                            if($promedio_num >= 70){
                                                $color_promedio = '#2ecc71'; // Verde Aprobado
                                                $bg_promedio = 'rgba(46, 204, 113, 0.1)';
                                            } else {
                                                $color_promedio = '#e74c3c'; // Rojo Reprobado
                                                $bg_promedio = 'rgba(231, 76, 60, 0.1)';
                                            }
                                        }
                                    ?>
                                    <td class="promedio-final" style="color: <?php echo $color_promedio; ?>; background-color: <?php echo $bg_promedio; ?>;">
                                        <?php echo $promedio; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="padding:40px; color: #adb5bd;">No se encontraron registros académicos para este alumno.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <button id="btnDescargar" class="btn-pdf">
                📄 DESCARGAR KÁRDEX EN PDF
            </button>

        </section>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    document.getElementById("btnDescargar").addEventListener("click", function() {
        const { jsPDF } = window.jspdf;
        let area = document.getElementById("areaDescarga");
        
        this.innerText = "GENERANDO PDF...";
        this.disabled = true;

        html2canvas(area, { 
            backgroundColor: "#142d3e",
            scale: 2,
            useCORS: true 
        }).then(canvas => {
            let imgData = canvas.toDataURL("image/png");
            let pdf = new jsPDF('p', 'mm', 'a4');
            let imgWidth = 190; 
            let imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
            pdf.save("Kardex_<?php echo $matricula; ?>.pdf");
            
            this.innerText = "📄 DESCARGAR KÁRDEX EN PDF";
            this.disabled = false;
        });
    });
</script>
</body>
</html>