<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

// Obtener ID del docente
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// Consulta de Grupos
$query = "SELECT g.id AS grupo_id, g.nombre_grupo, m.nombre AS materia_nombre 
          FROM grupos g 
          INNER JOIN materias m ON g.materia_id = m.id 
          WHERE g.docente_id = '$id_docente'";
$res_grupos = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Materias | ISIC</title>
    <style>
        :root {
            --bg-principal: #0f3145;
            --bg-secundario: #255b68;
            --accent: #2b6671;
            --texto: #ffffff;
            --exito: #2ecc71;
        }

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-principal);
            color: var(--texto);
            font-family: 'Segoe UI', sans-serif;
        }

        /* BARRA LATERAL FIJA */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background-color: var(--bg-secundario);
            border-right: 1px solid var(--accent);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: #adb5bd;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: var(--bg-principal);
            color: white;
            border-left: 4px solid var(--exito);
        }

        /* CONTENIDO */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .card-materia {
            background: var(--bg-secundario);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--accent);
            cursor: pointer;
            transition: 0.3s;
            width: 280px;
            text-align: center;
        }

        .card-materia:hover {
            transform: translateY(-5px);
            border-color: var(--exito);
        }

        .tabla-alumnos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--bg-secundario);
            border-radius: 10px;
            overflow: hidden;
        }

        .tabla-alumnos th { background: var(--accent); padding: 15px; text-align: left; }
        .tabla-alumnos td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        .btn-regresar {
            background: var(--accent);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 style="color: var(--exito); margin:0;">ISIC</h2>
            <small>Docente</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../docente.php">🏠 INICIO</a></li>
            <li><a href="docenteM.php" class="active">📚 MIS MATERIAS</a></li>
            <li><a href="../Calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
            <li><a href="../TareasDocente/docenteT.php">📂 TAREAS</a></li>
        </ul>
        <a href="../../auth/logout.php" style="margin-top:auto; padding:20px; text-align:center; color:#ffb3b3; text-decoration:none;">CERRAR SESIÓN</a>
    </aside>

    <main class="main-content">
        <div id="vista_principal">
            <h1>Mis Materias</h1>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <?php while($g = mysqli_fetch_assoc($res_grupos)): ?>
                    <div class="card-materia" onclick="verAlumnos(<?php echo $g['grupo_id']; ?>, '<?php echo $g['materia_nombre']; ?>')">
                        <small style="color: var(--exito);">Grupo: <?php echo $g['nombre_grupo']; ?></small>
                        <h3><?php echo $g['materia_nombre']; ?></h3>
                        <p style="font-size: 12px; color: #adb5bd;">Click para ver lista de alumnos</p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="vista_alumnos" style="display: none;">
            <button class="btn-regresar" onclick="regresar()">⬅ Volver a Materias</button>
            <h2 id="titulo_materia"></h2>
            <div id="tabla_alumnos_res"></div>
        </div>
    </main>

    <script>
    function verAlumnos(grupoId, materia) {
        document.getElementById('vista_principal').style.display = 'none';
        document.getElementById('vista_alumnos').style.display = 'block';
        document.getElementById('titulo_materia').innerText = "Lista de Alumnos - " + materia;

        fetch('get_alumnos.php?grupo_id=' + grupoId)
            .then(res => res.text())
            .then(html => { document.getElementById('tabla_alumnos_res').innerHTML = html; });
    }

    function regresar() {
        document.getElementById('vista_principal').style.display = 'block';
        document.getElementById('vista_alumnos').style.display = 'none';
    }
    </script>
</body>
</html>