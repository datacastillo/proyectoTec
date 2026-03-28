<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'] ?? 'Alumno';

$query_info = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_info = mysqli_query($conexion, $query_info);
$info_alumno = mysqli_fetch_assoc($res_info);

$alumno_id = $info_alumno['id'] ?? 0;
$matricula = $info_alumno['matricula'] ?? 'S/N';

// AQUÍ ESTÁ LA MAGIA: Agregamos hasta 6 unidades y contamos el total real de unidades
$query_calificaciones = "
    SELECT 
        m.nombre as materia,
        MAX(CASE WHEN u.numero_unit = 1 THEN cu.nota_final ELSE NULL END) as unidad_1,
        MAX(CASE WHEN u.numero_unit = 2 THEN cu.nota_final ELSE NULL END) as unidad_2,
        MAX(CASE WHEN u.numero_unit = 3 THEN cu.nota_final ELSE NULL END) as unidad_3,
        MAX(CASE WHEN u.numero_unit = 4 THEN cu.nota_final ELSE NULL END) as unidad_4,
        MAX(CASE WHEN u.numero_unit = 5 THEN cu.nota_final ELSE NULL END) as unidad_5,
        MAX(CASE WHEN u.numero_unit = 6 THEN cu.nota_final ELSE NULL END) as unidad_6,
        COUNT(DISTINCT u.id) as total_unidades
    FROM inscripciones i
    JOIN grupos g ON i.grupo_id = g.id
    JOIN materias m ON g.materia_id = m.id
    LEFT JOIN unidades u ON g.id = u.grupo_id
    LEFT JOIN calificaciones_unidades cu ON u.id = cu.unidad_id AND cu.alumno_id = i.alumno_id
    WHERE i.alumno_id = '$alumno_id'
    GROUP BY g.id, m.nombre
";
$res_calificaciones = mysqli_query($conexion, $query_calificaciones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Calificaciones | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* Barra Lateral */
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;}
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover, .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        /* Contenido Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden;}
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        /* Tarjeta de la tabla */
        .table-card { 
            background: #142d3e; 
            border-radius: 12px; 
            padding: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            border: 1px solid rgba(62, 146, 204, 0.2); 
            margin-top: 20px;
            overflow-x: auto; /* Permite scroll horizontal si no caben las 6 unidades */
        }
        
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { text-align: left; padding: 18px 10px; border-bottom: 2px solid #3e92cc; color: #fff; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        td { padding: 18px 10px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #adb5bd; transition: all 0.3s ease; font-size: 14px;}
        
        tr { transition: all 0.3s ease; }
        tr:hover td { background: rgba(62, 146, 204, 0.05); color: #fff; }
        
        /* Etiquetas (Badges) */
        .badge { 
            padding: 8px 16px; 
            border-radius: 50px; 
            font-size: 11px; 
            font-weight: 900; 
            letter-spacing: 1px; 
            display: inline-block; 
            min-width: 100px; 
            text-align: center;
        }
        .aprobado { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); box-shadow: 0 4px 10px rgba(46, 204, 113, 0.15); }
        .reprobado { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); box-shadow: 0 4px 10px rgba(231, 76, 60, 0.15); }
        .pendiente { background: rgba(241, 196, 15, 0.1); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.3); box-shadow: 0 4px 10px rgba(241, 196, 15, 0.15); }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../img/logoTec.png" alt="Logo" style="max-width: 120px; margin-bottom: 10px;">
            <div class="user-info">
                <span style="color:#3e92cc; font-size: 12px; font-weight: bold;">ALUMNO:</span><br>
                <b style="color: white; font-size: 14px;"><?php echo strtoupper($nombreAlumno); ?></b><br>
                <span style="color: #adb5bd; font-size: 12px;">Matrícula: <?php echo $matricula; ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="../Materias/Index.php">📚 MIS MATERIAS</a></li>
                <li class="active"><a href="calificaciones.php">📊 CALIFICACIONES</a></li>
                <li><a href="../Tareas/tareas.php">📝 TAREAS PENDIENTES</a></li>
                <li><a href="../Kardex/kardex.php">📜 MI KARDEX</a></li>
                <li style="margin-top: 30px;"><a href="../../auth/logout.php" style="color: #e74c3c;">🚪 CERRAR SESIÓN</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
        </header>

        <section style="padding: 40px 30px;">
            <h2 style="color: #fff; font-size: 2.2rem; margin-bottom: 5px;">Boleta de Calificaciones</h2>
            <p style="color: #adb5bd; font-size: 1rem; margin-bottom: 30px;">Consulta tu progreso general en las materias inscritas.</p>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th style="text-align: center;">U1</th>
                            <th style="text-align: center;">U2</th>
                            <th style="text-align: center;">U3</th>
                            <th style="text-align: center;">U4</th>
                            <th style="text-align: center;">U5</th>
                            <th style="text-align: center;">U6</th>
                            <th style="text-align: center;">Promedio</th>
                            <th style="text-align: center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($res_calificaciones && mysqli_num_rows($res_calificaciones) > 0):
                            while ($fila = mysqli_fetch_assoc($res_calificaciones)): 
                                $u1 = $fila['unidad_1'];
                                $u2 = $fila['unidad_2'];
                                $u3 = $fila['unidad_3'];
                                $u4 = $fila['unidad_4'];
                                $u5 = $fila['unidad_5'];
                                $u6 = $fila['unidad_6'];
                                $total_unidades = (int)$fila['total_unidades'];

                                $suma = 0;
                                $calificadas = 0;

                                // Contamos cuántas unidades realmente tienen calificación
                                if ($u1 !== null) { $suma += $u1; $calificadas++; }
                                if ($u2 !== null) { $suma += $u2; $calificadas++; }
                                if ($u3 !== null) { $suma += $u3; $calificadas++; }
                                if ($u4 !== null) { $suma += $u4; $calificadas++; }
                                if ($u5 !== null) { $suma += $u5; $calificadas++; }
                                if ($u6 !== null) { $suma += $u6; $calificadas++; }

                                // Revisamos si ya tiene todas las calificaciones según el total de unidades que tiene la materia
                                $tiene_todo = ($calificadas == $total_unidades && $total_unidades > 0);
                                $promedio = 0;

                                if ($tiene_todo) {
                                    $promedio = $suma / $total_unidades;
                                    
                                    if ($promedio >= 70) {
                                        $estado_clase = "aprobado";
                                        $estado_texto = "APROBADO";
                                    } else {
                                        $estado_clase = "reprobado";
                                        $estado_texto = "REPROBADO";
                                    }
                                } else {
                                    $estado_clase = "pendiente";
                                    $estado_texto = "EN CURSO";
                                }
                        ?>
                        <tr>
                            <td style="font-weight: 700; color: #fff;"><?php echo strtoupper($fila['materia']); ?></td>
                            <td style="text-align: center;"><?php echo $u1 !== null ? number_format($u1, 2) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo $u2 !== null ? number_format($u2, 2) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo $u3 !== null ? number_format($u3, 2) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo $u4 !== null ? number_format($u4, 2) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo $u5 !== null ? number_format($u5, 2) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo $u6 !== null ? number_format($u6, 2) : '-'; ?></td>
                            
                            <td style="text-align: center; font-weight: 800; color: <?php echo ($tiene_todo && $promedio >= 70) ? '#2ecc71' : ($tiene_todo ? '#e74c3c' : '#fff'); ?>;">
                                <?php echo $tiene_todo ? number_format($promedio, 2) : '-'; ?>
                            </td>
                            <td style="text-align: center;"><span class="badge <?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></span></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" style="padding: 40px; text-align: center; color: #adb5bd; font-style: italic;">No estás inscrito en ninguna materia actualmente.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>