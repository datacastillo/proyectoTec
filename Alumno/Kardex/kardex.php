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


 
$query_kardex = "
    SELECT 
        m.id AS materia_id, 
        m.nombre AS materia_nombre, 
        m.clave,
        u.numero_unit,
        cu.nota_final
    FROM materias m
    INNER JOIN grupos g ON m.id = g.materia_id
    INNER JOIN inscripciones i ON g.id = i.grupo_id
    INNER JOIN unidades u ON g.id = u.grupo_id
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
            'notas' => [1 => '-', 2 => '-', 3 => '-', 4 => '-']
        ];
    }
    if ($row['nota_final'] !== null) {
        $materias_notas[$m_id]['notas'][$row['numero_unit']] = $row['nota_final'];
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
        .kardex-table td { padding: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center; color: #e0e1dd; }
        .text-left { text-align: left !important; }
        .promedio-final { font-weight: bold; color: #2ecc71; background: rgba(46, 204, 113, 0.1); }
        
        /* Botón PDF */
        .btn-pdf { background: #3e92cc; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 30px; transition: 0.3s; font-size: 14px; letter-spacing: 1px; }
        .btn-pdf:hover { background: #2c7bb6; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(62, 146, 204, 0.4); }
        .btn-pdf:disabled { background: #555; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../img/logoTec.png" alt="Logo" style="max-width: 120px; margin-bottom: 10px;">
            <div class="user-info">
                <span style="color:#3e92cc; font-size: 12px; font-weight: bold;">ALUMNO:</span><br>
                <b style="color: white; font-size: 14px;"><?php echo strtoupper($nombreAlumno); ?></b><br>
                <span style="color: #adb5bd; font-size: 12px;">Matrícula: <?php echo $matricula; ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="../Materias/Index.php">📚 MIS MATERIAS</a></li>
                <li><a href="../Calificaciones/calificaciones.php">📊 CALIFICACIONES</a></li>
                <li><a href="../Tareas/tareas.php">📝 TAREAS PENDIENTES</a></li>
                <li class="active"><a href="kardex.php">📜 MI KARDEX</a></li>
                <li style="margin-top: 30px;"><a href="../../auth/logout.php" style="color: #e74c3c;">🚪 CERRAR SESIÓN</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
        </header>

        <section style="padding: 30px; display: flex; flex-direction: column; align-items: center;">
            
            <div id="areaDescarga" style="width: 100%; max-width: 900px; text-align: center;">
                <img src="../../img/logoTec.png" class="logo-kardex" alt="Logo Tec">
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
                            <th>PROM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($materias_notas) > 0): ?>
                            <?php foreach ($materias_notas as $m): ?>
                                <tr>
                                    <td class="text-left"><strong style="color: #3e92cc;"><?php echo $m['clave']; ?></strong> - <?php echo strtoupper($m['nombre']); ?></td>
                                    <?php 
                                    $suma = 0; $cont = 0;
                                    for ($i=1; $i <= 4; $i++): 
                                        $n = $m['notas'][$i];
                                        if (is_numeric($n)) { $suma += $n; $cont++; }
                                    ?>
                                        <td><?php echo is_numeric($n) ? number_format($n, 0) : '-'; ?></td>
                                    <?php endfor; ?>
                                    
                                    <td class="promedio-final">
                                        <?php echo ($cont > 0) ? round($suma / $cont, 0) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding:40px; color: #adb5bd;">No se encontraron registros académicos para este alumno.</td></tr>
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