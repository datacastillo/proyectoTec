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

// Consulta de información del alumno
$query_info = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_info = mysqli_query($conexion, $query_info);
$info_alumno = mysqli_fetch_assoc($res_info);

$alumno_id = $info_alumno['id'] ?? 0;
$matricula = $info_alumno['matricula'] ?? 'S/N';

// Consulta de materias dinámicas (Agregamos DISTINCT para evitar duplicados)
$query_materias = "SELECT DISTINCT g.id AS grupo_id, m.nombre, m.clave                   
                   FROM materias m
                   INNER JOIN grupos g ON m.id = g.materia_id
                   INNER JOIN inscripciones i ON g.id = i.grupo_id
                   WHERE i.alumno_id = '$alumno_id'";
$res_materias = mysqli_query($conexion, $query_materias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* Paleta de colores Azul (Estilo Docente) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* Barra Lateral */
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;}
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        /* Contenido Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        /* Tarjetas de Materias - AJUSTE PARA LAS 6 MATERIAS */
        .grid-materias { 
            display: grid; 
            /* Forzamos 3 columnas en pantallas grandes para que 6 materias se vean en 2 filas */
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            margin-top: 30px; 
        }

        /* Responsive: 2 columnas en pantallas medianas, 1 en celulares */
        @media (max-width: 1200px) {
            .grid-materias { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .grid-materias { grid-template-columns: 1fr; }
        }

        .card-materia { 
            background: linear-gradient(145deg, #142d3e, #102432); /* Toque de profundidad */
            border: 1px solid rgba(62, 146, 204, 0.2); 
            border-radius: 12px; 
            padding: 30px; 
            transition: all 0.4s ease; 
            cursor: pointer; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px; /* Altura uniforme */
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-materia:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
            border-color: #3e92cc; 
        }
        .card-title { font-size: 1.3rem; font-weight: 900; color: #fff; line-height: 1.3; margin-bottom: 10px; }
        .card-clave { font-size: 0.9rem; color: #3e92cc; font-weight: bold; background: rgba(62, 146, 204, 0.1); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;}
        .btn-entrar { background: rgba(62, 146, 204, 0.1); color: #3e92cc; border: 1px solid #3e92cc; padding: 12px 20px; border-radius: 8px; font-weight: bold; width: 100%; transition: all 0.3s ease; cursor: pointer; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;}
        .card-materia:hover .btn-entrar { background: #3e92cc; color: #fff; }
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
        </header>

        <section style="padding: 40px 30px;">
            <h2 style="color: #fff; font-size: 2.2rem; margin-bottom: 5px;">Bienvenido, <?php echo strtoupper($nombreAlumno); ?></h2>
            <p style="color: #adb5bd; font-size: 1.1rem; margin-bottom: 30px;">Selecciona una materia para ver sus detalles y recursos.</p>

            <div class="grid-materias">
                <?php 
                if($res_materias && mysqli_num_rows($res_materias) > 0) {
                    while($materia = mysqli_fetch_assoc($res_materias)) {
                        echo "
                    <div class='card-materia' onclick=\"location.href='detalle_materia.php?grupo_id=".$materia['grupo_id']."'\">
                        <div>
                            <div class='card-title'>".strtoupper($materia['nombre'])."</div>
                            <div class='card-clave'>CLAVE: ".$materia['clave']."</div>
                        </div>
                        <button class='btn-entrar'>Ir a la materia ➔</button>
                    </div>";
                    }
                } else {
                    echo "<div style='grid-column: 1 / -1; padding: 40px; background: #142d3e; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); text-align: center;'>
                            <h3 style='color: #fff; margin-bottom: 10px;'>Sin materias</h3>
                            <p style='color: #adb5bd;'>No tienes materias inscritas en este semestre.</p>
                          </div>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>