<?php
session_start();
require_once '../../config/db.php'; 

// 1. Validar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'] ?? 'Alumno';

if (!isset($_GET['grupo_id'])) {
    header("Location: Index.php");
    exit();
}

$grupo_id = mysqli_real_escape_string($conexion, $_GET['grupo_id']);

// Obtener el ID del alumno
$query_info = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_info = mysqli_query($conexion, $query_info);
$info_alumno = mysqli_fetch_assoc($res_info);
$alumno_id = $info_alumno['id'] ?? 0;
$matricula = $info_alumno['matricula'] ?? 'S/N';

// Obtener info de la Materia (Temporalmente sin el JOIN problemático del profesor)
$query_materia = "
    SELECT m.nombre as materia_nombre, m.clave
    FROM grupos g
    INNER JOIN materias m ON g.materia_id = m.id
    INNER JOIN inscripciones i ON g.id = i.grupo_id
    WHERE g.id = '$grupo_id' AND i.alumno_id = '$alumno_id' LIMIT 1";
$res_materia = mysqli_query($conexion, $query_materia);

if (mysqli_num_rows($res_materia) == 0) {
    echo "<script>alert('Error: No estás inscrito en este grupo o no existe.'); window.location.href='Index.php';</script>";
    exit();
}
$datos_materia = mysqli_fetch_assoc($res_materia);

// Valores temporales para el profesor mientras revisamos tu BD
$datos_materia['prof_nombre'] = "Profesor";
$datos_materia['prof_apellidos'] = "Asignado";

// Obtener Unidades y sus Tareas para este grupo
$query_unidades = "
    SELECT un.id as unidad_id, un.nombre_unidad as unidad_nombre, 
           t.id as tarea_id, t.titulo, t.descripcion, t.fecha_entrega_limite,
           e.id as entrega_id
    FROM unidades un
    LEFT JOIN tareas t ON un.id = t.unidad_id
    LEFT JOIN entregas e ON t.id = e.tarea_id AND e.alumno_id = '$alumno_id'
    WHERE un.grupo_id = '$grupo_id'
    ORDER BY un.id ASC, t.fecha_entrega_limite ASC";
$res_unidades = mysqli_query($conexion, $query_unidades);

// Agrupar los resultados por unidad en un arreglo
$unidades = [];
if ($res_unidades && mysqli_num_rows($res_unidades) > 0) {
    while($row = mysqli_fetch_assoc($res_unidades)) {
        $id_u = $row['unidad_id'];
        if(!isset($unidades[$id_u])) {
            $unidades[$id_u] = [
                'nombre' => $row['unidad_nombre'],
                'tareas' => []
            ];
        }
        if($row['tarea_id'] != null) {
            $unidades[$id_u]['tareas'][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($datos_materia['materia_nombre']); ?> | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover, .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        .main-content { flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between;}
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        .btn-volver { background: #e0e1dd; color: #0d1b2a; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 13px; transition: 0.3s; }
        .btn-volver:hover { background: #adb5bd; }

        .header-materia { background: linear-gradient(135deg, #142d3e 0%, #0d1b2a 100%); padding: 40px 30px; border-bottom: 1px solid #3e92cc; }
        .header-materia h1 { font-size: 2.5rem; color: #fff; margin-bottom: 10px; }
        .header-materia p { color: #3e92cc; font-size: 1.1rem; font-weight: bold; }
        .header-materia span { color: #adb5bd; font-size: 0.9rem; font-weight: normal; display: block; margin-top: 5px;}

        .unidades-container { padding: 30px; }
        
        /* DISEÑO PLUS: Unidades */
        .unidad-box { 
            background: #142d3e; 
            border: 1px solid rgba(62, 146, 204, 0.2); 
            border-radius: 12px; 
            padding: 25px; 
            margin-bottom: 25px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); 
        }
        .unidad-title { font-size: 1.5rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 20px; }

        /* DISEÑO PLUS: Tareas con efecto Hover */
        .tarea-item { 
            background: #0d1b2a; 
            border-left: 4px solid #3e92cc; 
            padding: 20px 25px; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: all 0.3s ease; 
            border-top: 1px solid transparent;
            border-right: 1px solid transparent;
            border-bottom: 1px solid transparent;
        }
        .tarea-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            border-color: rgba(62, 146, 204, 0.3);
            border-left: 4px solid #3e92cc;
        }
        .tarea-info h4 { color: #fff; font-size: 1.1rem; margin-bottom: 5px; }
        .tarea-info p { color: #adb5bd; font-size: 0.9rem; }
        
        /* DISEÑO PLUS: Fechas como etiquetas */
        .tarea-fecha { 
            color: #e74c3c; 
            font-size: 0.8rem; 
            font-weight: 600; 
            margin-top: 8px;
            background: rgba(231, 76, 60, 0.1);
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        /* DISEÑO PLUS: Botones estilo Píldora */
        .btn-subir { 
            background: linear-gradient(135deg, #3e92cc 0%, #2c7bb6 100%);
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 50px; 
            cursor: pointer; 
            font-weight: 800; 
            transition: 0.3s; 
            font-size: 11px; 
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(62, 146, 204, 0.3);
        }
        .btn-subir:hover { 
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(62, 146, 204, 0.5);
        }
        .entregada { 
            background: rgba(46, 204, 113, 0.1); 
            color: #2ecc71; 
            border: 1px solid rgba(46, 204, 113, 0.3); 
            padding: 8px 18px; 
            border-radius: 50px; 
            font-weight: 900; 
            font-size: 11px; 
            letter-spacing: 1px;
            display: inline-block;
        }

        /* Modal (Igual que antes) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
        .modal-content { background: #142d3e; padding: 30px; border-radius: 10px; width: 350px; text-align: center; border: 1px solid #3e92cc; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .modal-content h2 { color: #fff; margin-bottom: 5px; font-size: 1.5rem; }
        .modal-content p { font-size: 13px; color: #adb5bd; margin-bottom: 20px; }
        .file-input { width: 100%; padding: 10px; background: #0d1b2a; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 5px; margin-bottom: 20px; }
        .btn-group { display: flex; gap: 10px; }
        .btn-enviar { background: #3e92cc; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.3s; }
        .btn-enviar:hover { background: #2c7bb6; }
        .btn-cerrar { background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; flex: 1; transition: 0.3s; }
        .btn-cerrar:hover { background: #c0392b; }
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
                <li class="active"><a href="Index.php">📚 MIS MATERIAS</a></li>
                <li><a href="../Calificaciones/calificaciones.php">📊 CALIFICACIONES</a></li>
                <li><a href="../Tareas/tareas.php">📝 TAREAS PENDIENTES</a></li>
                <li><a href="../Kardex/kardex.php">📜 MI KARDEX</a></li>
                <li style="margin-top: 30px;"><a href="../../auth/logout.php" style="color: #e74c3c;">🚪 CERRAR SESIÓN</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
            <a href="Index.php" class="btn-volver">⬅ Volver a Mis Materias</a>
        </header>

        <section class="header-materia">
            <h1><?php echo strtoupper(htmlspecialchars($datos_materia['materia_nombre'])); ?></h1>
            <p>Clave: <?php echo htmlspecialchars($datos_materia['clave']); ?></p>
            <span>👨‍🏫 Impartida por: <?php echo htmlspecialchars($datos_materia['prof_nombre'] . " " . $datos_materia['prof_apellidos']); ?></span>
        </section>

        <section class="unidades-container">
            <?php 
            if(count($unidades) > 0) {
                foreach($unidades as $u) {
                    echo "<div class='unidad-box'>";
                    echo "<h3 class='unidad-title'>📚 " . htmlspecialchars($u['nombre']) . "</h3>";
                    
                    if(count($u['tareas']) > 0) {
                        foreach($u['tareas'] as $t) {
                            $fecha_limite = strtotime($t['fecha_entrega_limite']);
                            $fecha_formato = ($t['fecha_entrega_limite']) ? date("d/m/Y H:i", $fecha_limite) : "Sin fecha límite";
                            $ya_entregado = !is_null($t['entrega_id']);

                            echo "<div class='tarea-item'>";
                            echo "<div class='tarea-info'>";
                            echo "<h4>" . htmlspecialchars($t['titulo']) . "</h4>";
                            echo "<p>" . htmlspecialchars($t['descripcion']) . "</p>";
                            echo "<div class='tarea-fecha'>⏳ Límite: " . $fecha_formato . "</div>";
                            echo "</div>";
                            
                            echo "<div class='tarea-accion'>";
                            if($ya_entregado) {
                                echo "<span class='entregada'>✔ ENTREGADA</span>";
                            } else {
                                echo "<button class='btn-subir' onclick='abrirModal(".$t['tarea_id'].")'>↑ SUBIR PDF</button>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='color:#adb5bd; font-size:14px; font-style:italic;'>No hay tareas asignadas en esta unidad por el momento.</p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<div style='text-align:center; padding: 40px;'><h3 style='color:#fff;'>Aún no hay unidades</h3><p style='color:#adb5bd;'>El profesor todavía no ha creado contenido para esta clase.</p></div>";
            }
            ?>
        </section>
    </main>
</div>

<div id="modalPDF" class="modal">
    <div class="modal-content">
        <h2>Subir Archivo</h2>
        <p>Asegúrate de que tu archivo esté en formato <strong>.PDF</strong></p>
        
        <form action="../Tareas/subir_tarea.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tarea_id" id="modal_tarea_id">
            <input type="file" name="archivo_pdf" accept="application/pdf" required class="file-input">
            
            <div class="btn-group">
                <button type="button" class="btn-cerrar" onclick="cerrarModal()">CANCELAR</button>
                <button type="submit" class="btn-enviar">ENVIAR TAREA</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal(id) {
        document.getElementById("modal_tarea_id").value = id;
        document.getElementById("modalPDF").style.display = "flex";
    }
    function cerrarModal() {
        document.getElementById("modalPDF").style.display = "none";
    }
</script>

</body>
</html>