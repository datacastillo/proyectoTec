<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id'];
$nombre_docente = $_SESSION['nombre_completo'] ?? 'Docente';

// Obtener el ID del grupo desde la URL
$grupo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Obtener información de la materia y grupo para el encabezado
$query_info = "SELECT m.nombre as materia, g.nombre_grupo 
               FROM grupos g 
               JOIN materias m ON g.materia_id = m.id 
               WHERE g.id = '$grupo_id'";
$res_info = mysqli_query($conexion, $query_info);
$info = mysqli_fetch_assoc($res_info);

// 2. Obtener la lista de alumnos inscritos en este grupo
// Relacionamos 'inscripciones' con 'alumnos'
$query_alumnos = "SELECT a.id, a.nombre, a.matricula, a.correo 
                  FROM alumnos a
                  JOIN inscripciones i ON a.id = i.alumno_id
                  WHERE i.grupo_id = '$grupo_id'
                  ORDER BY a.nombre ASC";
$res_alumnos = mysqli_query($conexion, $query_alumnos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Alumnos | ISIC</title>
    <link rel="stylesheet" href="../docente.css">
    <style>
        .tabla-alumnos {
            width: 100%;
            border-collapse: collapse;
            background: #142d3e;
            color: white;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        .tabla-alumnos th {
            background: #3e92cc;
            padding: 15px;
            text-align: left;
        }
        .tabla-alumnos td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .tabla-alumnos tr:hover { background: rgba(62, 146, 204, 0.1); }
        .btn-regresar {
            display: inline-block;
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #3e92cc;
            color: #3e92cc;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-regresar:hover { background: #3e92cc; color: white; }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <span>DOCENTE:<br><b><?php echo strtoupper($nombre_docente); ?></b></span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../docente.php">🏠 INICIO</a></li>
                    <li class="active"><a href="docenteM.php">📘 MIS MATERIAS</a></li>
                    <li><a href="../Calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
                    <li><a href="../Tareas/tareas.php">📂 TAREAS</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="isic-box">LISTA DE ASISTENCIA</div>
            </header>

            <section style="padding: 40px;">
                <a href="docenteM.php" class="btn-regresar">← Volver a Materias</a>
                
                <h2 style="margin: 0; font-weight: 300;"><?php echo strtoupper($info['materia']); ?></h2>
                <p style="color: #3e92cc; font-weight: bold; margin-bottom: 30px;">GRUPO: <?php echo $info['nombre_grupo']; ?></p>

                <table class="tabla-alumnos">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nombre del Alumno</th>
                            <th>Correo Institucional</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($res_alumnos) > 0): 
                            while($al = mysqli_fetch_assoc($res_alumnos)): ?>
                                <tr>
                                    <td style="color: #3e92cc; font-weight: bold;"><?php echo $al['matricula']; ?></td>
                                    <td><?php echo strtoupper($al['nombre']); ?></td>
                                    <td><?php echo $al['correo']; ?></td>
                                    <td><span style="color: #2ecc71;">● Activo</span></td>
                                </tr>
                            <?php endwhile; 
                        else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: #555;">
                                    No hay alumnos inscritos en este grupo todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>